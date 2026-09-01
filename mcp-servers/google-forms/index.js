#!/usr/bin/env node
/**
 * MCP server — Google Forms API.
 *
 * Conventions and setup: README.md in this folder.
 *
 * Design note: `forms.create` accepts only info.title / info.documentTitle.
 * Description and every question must go through batchUpdate afterwards. The
 * `forms_add_items` tool exists so that a questionnaire can be described in a
 * flat list instead of hand-written batchUpdate JSON.
 */
import { Server } from '@modelcontextprotocol/sdk/server/index.js';
import { StdioServerTransport } from '@modelcontextprotocol/sdk/server/stdio.js';
import {
  CallToolRequestSchema,
  ListToolsRequestSchema,
} from '@modelcontextprotocol/sdk/types.js';
import { api } from './auth.js';
import { ITEM_TYPES, toCreateItem } from './items.js';

const FORMS = 'https://forms.googleapis.com/v1/forms';
const DRIVE = 'https://www.googleapis.com/drive/v3/files';

/* ------------------------------------------------------------------ items -- */

/* ------------------------------------------------------------------ tools -- */

const TOOLS = [
  {
    name: 'forms_create',
    description:
      'Create an empty Google Form. Only the title can be set here — description and all questions ' +
      'must follow via forms_set_info / forms_add_items. Returns formId, responderUri and the edit URL.',
    inputSchema: {
      type: 'object',
      properties: {
        title: { type: 'string', description: 'Title shown to respondents.' },
        documentTitle: { type: 'string', description: 'File name in Drive. Defaults to title.' },
      },
      required: ['title'],
    },
  },
  {
    name: 'forms_set_info',
    description: 'Set the form title and/or description (the text under the title).',
    inputSchema: {
      type: 'object',
      properties: {
        formId: { type: 'string' },
        title: { type: 'string' },
        description: { type: 'string' },
      },
      required: ['formId'],
    },
  },
  {
    name: 'forms_add_items',
    description:
      'Append questions to a form from a flat list — the normal way to build a questionnaire. ' +
      'Item types: SECTION (page break), TEXT, PARAGRAPH, CHOICE, CHECKBOX, DROPDOWN, SCALE, DATE, INFO. ' +
      'Each item: {type, title, description?, required?, options?, other?, low?, high?}. ' +
      'Items are appended in the given order after any existing ones.',
    inputSchema: {
      type: 'object',
      properties: {
        formId: { type: 'string' },
        items: {
          type: 'array',
          items: {
            type: 'object',
            properties: {
              type: { type: 'string', enum: ITEM_TYPES },
              title: { type: 'string' },
              description: { type: 'string' },
              required: { type: 'boolean' },
              options: { type: 'array', items: { type: 'string' } },
              other: { type: 'boolean', description: 'Add an "Other" free-text option.' },
              low: { type: 'number' },
              high: { type: 'number' },
              lowLabel: { type: 'string' },
              highLabel: { type: 'string' },
              includeYear: { type: 'boolean' },
              includeTime: { type: 'boolean' },
            },
            required: ['type', 'title'],
          },
        },
        startIndex: {
          type: 'number',
          description: 'Where to insert. Omit to append at the end.',
        },
      },
      required: ['formId', 'items'],
    },
  },
  {
    name: 'forms_get',
    description: 'Read the full structure of a form, including item ids — needed before editing or deleting items.',
    inputSchema: {
      type: 'object',
      properties: { formId: { type: 'string' } },
      required: ['formId'],
    },
  },
  {
    name: 'forms_batch_update',
    description:
      'Raw batchUpdate for anything forms_add_items does not cover (updateItem, deleteItem, moveItem, ' +
      'updateSettings). Pass the requests array exactly as the Forms API expects it.',
    inputSchema: {
      type: 'object',
      properties: {
        formId: { type: 'string' },
        requests: { type: 'array', items: { type: 'object' } },
      },
      required: ['formId', 'requests'],
    },
  },
  {
    name: 'forms_publish',
    description:
      'Publish a form and set whether it accepts responses. Forms created through the API after ' +
      '30 June 2026 are unpublished by default — without this call the responder link stays dead.',
    inputSchema: {
      type: 'object',
      properties: {
        formId: { type: 'string' },
        published: { type: 'boolean', default: true },
        acceptingResponses: { type: 'boolean', default: true },
      },
      required: ['formId'],
    },
  },
  {
    name: 'forms_list_responses',
    description: 'List submitted responses of a form.',
    inputSchema: {
      type: 'object',
      properties: {
        formId: { type: 'string' },
        pageSize: { type: 'number' },
        pageToken: { type: 'string' },
      },
      required: ['formId'],
    },
  },
  {
    name: 'forms_share',
    description:
      'Share the form file with one address (Drive API). Note: no anyone-with-the-link sharing here — ' +
      'a questionnaire holding client analysis should not sit on a public link.',
    inputSchema: {
      type: 'object',
      properties: {
        formId: { type: 'string' },
        emailAddress: { type: 'string' },
        role: { type: 'string', enum: ['reader', 'commenter', 'writer'], default: 'writer' },
        sendNotification: { type: 'boolean', default: false },
      },
      required: ['formId', 'emailAddress'],
    },
  },
];

/* ---------------------------------------------------------------- handlers -- */

async function handle(name, a) {
  switch (name) {
    case 'forms_create': {
      const info = { title: a.title };
      if (a.documentTitle) info.documentTitle = a.documentTitle;
      const form = await api('POST', FORMS, { info });
      return {
        formId: form.formId,
        responderUri: form.responderUri,
        editUrl: `https://docs.google.com/forms/d/${form.formId}/edit`,
        note: 'Empty form. Add description with forms_set_info, questions with forms_add_items, then forms_publish.',
      };
    }

    case 'forms_set_info': {
      const info = {};
      const fields = [];
      if (a.title !== undefined) { info.title = a.title; fields.push('title'); }
      if (a.description !== undefined) { info.description = a.description; fields.push('description'); }
      if (!fields.length) throw new Error('Nothing to set — pass title and/or description.');
      return api('POST', `${FORMS}/${a.formId}:batchUpdate`, {
        requests: [{ updateFormInfo: { info, updateMask: fields.join(',') } }],
      });
    }

    case 'forms_add_items': {
      let start = a.startIndex;
      if (start === undefined) {
        const form = await api('GET', `${FORMS}/${a.formId}`);
        start = (form.items || []).length;
      }
      const requests = a.items.map((spec, i) => toCreateItem(spec, start + i));
      const res = await api('POST', `${FORMS}/${a.formId}:batchUpdate`, { requests });
      return { added: requests.length, startIndex: start, replies: res.replies };
    }

    case 'forms_get':
      return api('GET', `${FORMS}/${a.formId}`);

    case 'forms_batch_update':
      return api('POST', `${FORMS}/${a.formId}:batchUpdate`, { requests: a.requests });

    case 'forms_publish':
      return api('POST', `${FORMS}/${a.formId}:setPublishSettings`, {
        publishSettings: {
          publishState: {
            isPublished: a.published !== false,
            isAcceptingResponses: a.acceptingResponses !== false,
          },
        },
      });

    case 'forms_list_responses': {
      const q = new URLSearchParams();
      if (a.pageSize) q.set('pageSize', String(a.pageSize));
      if (a.pageToken) q.set('pageToken', a.pageToken);
      const qs = q.toString();
      return api('GET', `${FORMS}/${a.formId}/responses${qs ? `?${qs}` : ''}`);
    }

    case 'forms_share': {
      const q = new URLSearchParams({
        sendNotificationEmail: String(!!a.sendNotification),
        fields: 'id,role,type,emailAddress',
      });
      return api('POST', `${DRIVE}/${a.formId}/permissions?${q}`, {
        type: 'user',
        role: a.role || 'writer',
        emailAddress: a.emailAddress,
      });
    }

    default:
      throw new Error(`Unknown tool: ${name}`);
  }
}

/* ------------------------------------------------------------------ wire -- */

const server = new Server(
  { name: 'google-forms', version: '0.1.0' },
  { capabilities: { tools: {} } }
);

server.setRequestHandler(ListToolsRequestSchema, async () => ({ tools: TOOLS }));

server.setRequestHandler(CallToolRequestSchema, async (req) => {
  try {
    const result = await handle(req.params.name, req.params.arguments || {});
    return { content: [{ type: 'text', text: JSON.stringify(result, null, 2) }] };
  } catch (err) {
    return {
      isError: true,
      content: [{ type: 'text', text: String(err?.message || err) }],
    };
  }
});

await server.connect(new StdioServerTransport());
