/**
 * Shared OAuth handling for the Google Forms MCP server.
 *
 * Uses an installed-app ("Desktop") OAuth client. A service account is not an
 * option here: without Workspace domain-wide delegation it cannot own or create
 * forms in a personal Google account.
 *
 * Credentials come from the environment, the refresh token from a local file.
 * Neither belongs in the repository.
 */
import { OAuth2Client } from 'google-auth-library';
import { readFile, writeFile, mkdir } from 'node:fs/promises';
import { dirname, join } from 'node:path';
import { fileURLToPath } from 'node:url';
import { homedir } from 'node:os';

const HERE = dirname(fileURLToPath(import.meta.url));

export const SCOPES = [
  'https://www.googleapis.com/auth/forms.body',
  'https://www.googleapis.com/auth/forms.responses.readonly',
  'https://www.googleapis.com/auth/drive.file',
];

export const TOKEN_PATH =
  process.env.GOOGLE_FORMS_TOKEN_PATH ||
  join(homedir(), '.config', 'mcp-google-forms', 'token.json');

/** Load client id/secret from env, or from .env next to the repo root. */
async function loadClientCredentials() {
  let id = process.env.GOOGLE_OAUTH_CLIENT_ID;
  let secret = process.env.GOOGLE_OAUTH_CLIENT_SECRET;

  if (!id || !secret) {
    // fall back to the repo .env (two levels up from mcp-servers/google-forms)
    try {
      const envText = await readFile(join(HERE, '..', '..', '.env'), 'utf8');
      for (const line of envText.split('\n')) {
        const m = line.match(/^\s*(?:export\s+)?([A-Z0-9_]+)\s*=\s*(.*)\s*$/);
        if (!m) continue;
        const v = m[2].replace(/^["']|["']$/g, '');
        if (m[1] === 'GOOGLE_OAUTH_CLIENT_ID' && !id) id = v;
        if (m[1] === 'GOOGLE_OAUTH_CLIENT_SECRET' && !secret) secret = v;
      }
    } catch {
      /* no .env — fall through to the error below */
    }
  }

  if (!id || !secret) {
    throw new Error(
      'GOOGLE_OAUTH_CLIENT_ID / GOOGLE_OAUTH_CLIENT_SECRET are not set. ' +
        'See mcp-servers/google-forms/README.md, step 1.'
    );
  }
  return { id, secret };
}

/** OAuth client without a token — used by the bootstrap flow. */
export async function bareClient(redirectUri) {
  const { id, secret } = await loadClientCredentials();
  return new OAuth2Client({ clientId: id, clientSecret: secret, redirectUri });
}

/** OAuth client with the stored refresh token, ready to call the API. */
export async function authedClient() {
  const { id, secret } = await loadClientCredentials();
  let token;
  try {
    token = JSON.parse(await readFile(TOKEN_PATH, 'utf8'));
  } catch {
    throw new Error(
      `No token at ${TOKEN_PATH}. Run once: npm run auth  (see README.md, step 2).`
    );
  }
  const client = new OAuth2Client({ clientId: id, clientSecret: secret });
  client.setCredentials(token);
  return client;
}

export async function saveToken(token) {
  await mkdir(dirname(TOKEN_PATH), { recursive: true });
  await writeFile(TOKEN_PATH, JSON.stringify(token, null, 2), { mode: 0o600 });
  return TOKEN_PATH;
}

/**
 * Authenticated JSON request. Throws with the API's own error text — a silent
 * failure here would be much harder to diagnose than a loud one.
 */
export async function api(method, url, body) {
  const client = await authedClient();
  const { token } = await client.getAccessToken();
  if (!token) throw new Error('Could not obtain an access token — re-run: npm run auth');

  const res = await fetch(url, {
    method,
    headers: {
      Authorization: `Bearer ${token}`,
      'Content-Type': 'application/json',
    },
    body: body === undefined ? undefined : JSON.stringify(body),
  });

  const text = await res.text();
  if (!res.ok) {
    throw new Error(`${method} ${url} → HTTP ${res.status}\n${text.slice(0, 2000)}`);
  }
  return text ? JSON.parse(text) : {};
}
