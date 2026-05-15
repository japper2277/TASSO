# Preview Site Auth (Basic Auth via Cloudflare Pages)

Gates `preview.tassofilm.com` behind a username/password prompt so only the
client (and you) can see the staging build. Production (`tassofilm.com`)
is unaffected.

## How it works

`functions/_middleware.js` runs on every request to the Cloudflare Pages
site. If the build is from the `main` branch (production), traffic passes
through untouched. On any other branch (staging), it checks the
`Authorization` header against `PREVIEW_USER` / `PREVIEW_PASS` set in the
Pages environment. No match → returns `401` with `WWW-Authenticate`, which
makes the browser show its native username/password popup.

Browser caches the credentials for the session, so the client sees the
prompt once per device.

## Setup (one-time, after Cloudflare Pages is connected)

1. Cloudflare dashboard → **Workers & Pages** → your Pages project.
2. **Settings → Environment Variables**.
3. Click **Add variable** twice, with **Environment** set to **Preview**
   (leave **Production** blank so prod stays public):
   - `PREVIEW_USER` — pick a username (e.g., `tasso`)
   - `PREVIEW_PASS` — pick a password (e.g., `openssl rand -base64 12`)
   Mark both **Encrypted**.
4. Trigger a new staging deploy (push to `staging` branch or hit the
   "Retry deployment" button).
5. Visit `preview.tassofilm.com` — you should see the browser auth popup.
   Enter the credentials. Page loads.

## Sharing with the client

Tell the client:
- URL: `https://preview.tassofilm.com`
- Username: `<PREVIEW_USER value>`
- Password: `<PREVIEW_PASS value>`

They paste those once. Their browser remembers them. They never see the
prompt again unless they clear cookies or switch browsers.

## Rotating the password

If you ever need to revoke access:
1. Generate a new `PREVIEW_PASS` in Cloudflare → Environment Variables.
2. Trigger a redeploy of the staging branch.
3. Send the new password to anyone who still needs access.
4. Anyone with the old credentials gets bumped to the auth prompt on next
   request.
