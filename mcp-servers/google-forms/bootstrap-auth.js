#!/usr/bin/env node
/**
 * One-time OAuth flow. Run: npm run auth
 *
 * Starts a local listener, prints the consent URL, catches the redirect and
 * stores the refresh token. After this the MCP server runs unattended.
 */
import { createServer } from 'node:http';
import { bareClient, saveToken, SCOPES, TOKEN_PATH } from './auth.js';

const PORT = Number(process.env.GOOGLE_FORMS_AUTH_PORT || 4753);

// Loopback must be the IP form. Google rejects http://localhost for newer
// desktop clients with a bare "invalid_request", which is hard to diagnose.
const HOST = process.env.GOOGLE_FORMS_AUTH_HOST || '127.0.0.1';
const REDIRECT = `http://${HOST}:${PORT}/callback`;

const client = await bareClient(REDIRECT);

const url = client.generateAuthUrl({
  access_type: 'offline',
  prompt: 'consent', // force a refresh_token even on repeat runs
  scope: SCOPES,
});

console.log('\nOpen this URL in the browser of the account that should own the forms:\n');
console.log(url);
console.log(`\nWaiting for the redirect on ${REDIRECT} …\n`);

const server = createServer(async (req, res) => {
  if (!req.url?.startsWith('/callback')) {
    res.writeHead(404).end();
    return;
  }
  const code = new URL(req.url, `http://${HOST}:${PORT}`).searchParams.get('code');
  if (!code) {
    res.writeHead(400, { 'Content-Type': 'text/plain; charset=utf-8' }).end('No code in redirect.');
    return;
  }
  try {
    const { tokens } = await client.getToken(code);
    if (!tokens.refresh_token) {
      throw new Error(
        'Google returned no refresh_token. Revoke the app under ' +
          'myaccount.google.com/permissions and run this again.'
      );
    }
    const path = await saveToken(tokens);
    res.writeHead(200, { 'Content-Type': 'text/plain; charset=utf-8' }).end(
      'Done. You can close this tab.'
    );
    console.log(`Token stored: ${path}`);
    console.log('The MCP server can now run unattended.\n');
    server.close();
    process.exit(0);
  } catch (err) {
    res.writeHead(500, { 'Content-Type': 'text/plain; charset=utf-8' }).end(String(err));
    console.error(err);
    server.close();
    process.exit(1);
  }
});

server.listen(PORT, HOST);
console.log(`(token will be written to ${TOKEN_PATH})`);
