# Resync Button: Notion → GitHub Actions

One click in Notion fires the `Sync photos from Notion` workflow, and the site picks up the new images within ~30s–2min.

## Architecture

```
[Notion "Resync" button]
        │  POST (with Bearer PAT)
        ▼
[GitHub REST API: workflow dispatch]
        │  triggers
        ▼
[.github/workflows/sync-from-notion.yml]
        │  pulls images from Notion DB,
        │  writes images/manifest.json,
        │  commits + pushes
        ▼
[GitHub Pages rebuild → tasso site updated]
```

The workflow already has `workflow_dispatch:` enabled (verified in `.github/workflows/sync-from-notion.yml`), so no workflow edits are required.

## What you need

- A Notion **paid plan** (webhook button actions are paid-plan only).
- A GitHub **fine-grained personal access token (PAT)** scoped to this repo with `Actions: Read and write` permission.

## Step 1 — Create the GitHub PAT

1. Go to https://github.com/settings/personal-access-tokens/new
2. Token name: `notion-resync-button`
3. Resource owner: `japper2277`
4. Repository access: **Only select repositories** → `japper2277/TASSO`
5. Repository permissions:
   - **Actions**: `Read and write`
   - **Metadata**: `Read-only` (auto-added)
6. Expiration: pick something long (1 year) and set a calendar reminder to rotate it.
7. Click **Generate token**, copy it. Starts with `github_pat_...`. **You will not see it again.**

## Step 2 — Add the button in Notion

On the "Tasso Photos" parent page (https://www.notion.so/3610a6de99678127a795cf8f00d40bf8):

1. Type `/button` and pick **Button**.
2. Label it `Resync site`.
3. Under **Then do this** → **Add action** → **Send webhook**.
4. Configure the webhook exactly as below:

| Field | Value |
|---|---|
| **URL** | `https://api.github.com/repos/japper2277/TASSO/actions/workflows/sync-from-notion.yml/dispatches` |
| **Method** | `POST` (only option Notion offers) |

5. Click **Add custom header** twice and add:

| Key | Value |
|---|---|
| `Accept` | `application/vnd.github+json` |
| `Authorization` | `Bearer github_pat_xxxxxxxxxxxx` (your PAT from Step 1) |
| `X-GitHub-Api-Version` | `2022-11-28` |
| `User-Agent` | `notion-resync-button` |

6. **Body** (raw JSON):

```json
{ "ref": "main" }
```

GitHub requires `ref` (the branch to dispatch on). `main` is correct for this repo.

7. Save the button. Click it once to test.

## Step 3 — Verify it worked

- Open https://github.com/japper2277/TASSO/actions
- You should see a new "Sync photos from Notion" run within a few seconds, source = "manually triggered".
- A successful run that finds new images will commit `chore: sync photos from Notion [skip ci]` and push. GitHub Pages redeploys automatically.
- If nothing happens: the button in Notion will surface the HTTP response. `204 No Content` = success. `401` = bad PAT. `404` = PAT lacks Actions write on this repo, or wrong ref/workflow filename. `422` = body or `ref` invalid.

## Notes & gotchas

- GitHub returns `204` (empty body) on success. Notion's webhook UI will show this as a successful call.
- Workflow dispatches are rate-limited but very generously — clicking the button a few times is fine.
- The PAT lives only inside the Notion button config. Don't paste it anywhere else. Anyone with edit access to that Notion page can read it back, so keep the page private.
- Rotate the PAT before its expiration date; update the `Authorization` header value in the button.

## Fallback: if Notion buttons can't send the `Authorization` header

(Should not be needed — Notion paid plans support arbitrary custom headers — but kept here in case Notion ever restricts this.)

Stand up a tiny **Cloudflare Worker** (free tier) that holds the PAT as a secret and proxies the call:

```js
// worker.js
export default {
  async fetch(req, env) {
    if (req.method !== 'POST') return new Response('Method Not Allowed', { status: 405 });
    if (req.headers.get('x-resync-key') !== env.SHARED_SECRET) {
      return new Response('Unauthorized', { status: 401 });
    }
    const r = await fetch(
      'https://api.github.com/repos/japper2277/TASSO/actions/workflows/sync-from-notion.yml/dispatches',
      {
        method: 'POST',
        headers: {
          'Accept': 'application/vnd.github+json',
          'Authorization': `Bearer ${env.GITHUB_PAT}`,
          'X-GitHub-Api-Version': '2022-11-28',
          'User-Agent': 'notion-resync-worker',
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({ ref: 'main' }),
      },
    );
    return new Response(r.statusText, { status: r.status });
  },
};
```

Set secrets: `wrangler secret put GITHUB_PAT` and `wrangler secret put SHARED_SECRET`.
Point the Notion button at the Worker URL with header `x-resync-key: <SHARED_SECRET>` and empty body. The PAT never leaves Cloudflare.

## Reference: equivalent curl (for local testing)

```bash
curl -X POST \
  -H "Accept: application/vnd.github+json" \
  -H "Authorization: Bearer github_pat_xxxxxxxxxxxx" \
  -H "X-GitHub-Api-Version: 2022-11-28" \
  https://api.github.com/repos/japper2277/TASSO/actions/workflows/sync-from-notion.yml/dispatches \
  -d '{"ref":"main"}'
```

A `204` response with no body means the workflow was dispatched successfully.
