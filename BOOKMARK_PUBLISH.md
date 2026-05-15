# Publish Bookmarks (free Notion plan)

One-click "Publish" and "Preview" via a browser bookmark. No Notion paid plan, no terminal.

## Architecture

```
[Client clicks bookmark]
        │  GET https://tasso-publish.workers.dev/?key=...&ref=main
        ▼
[Cloudflare Worker]
        │  POST with Bearer PAT
        ▼
[GitHub Actions workflow_dispatch]
        │  runs sync-from-notion.yml on the requested branch
        ▼
[Branch updated → Cloudflare Pages redeploys → site live]
```

## What you need

- A Cloudflare account (free).
- A GitHub fine-grained PAT scoped to `japper2277/TASSO` with `Actions: Read and write`.
- `wrangler` CLI installed (`npm i -g wrangler`) **OR** just paste the worker code in the Cloudflare dashboard.

## Step 1 — Deploy the Worker

Code lives at `worker/publish.js` in this repo. Two ways to deploy:

### Option A — CLI
```bash
cd worker
wrangler login
wrangler deploy
wrangler secret put GITHUB_PAT       # paste your GitHub PAT
wrangler secret put SHARED_SECRET    # any random string, e.g. `openssl rand -hex 16`
```
The deploy will print a URL like `https://tasso-publish.<your-account>.workers.dev`.

### Option B — Dashboard
1. Cloudflare dashboard → **Workers & Pages** → **Create application** → **Create Worker**.
2. Name it `tasso-publish`, click **Deploy**, then **Edit code**.
3. Paste the contents of `worker/publish.js` into the editor. Save and deploy.
4. **Settings → Variables → Environment Variables → Add (Encrypted)**:
   - `GITHUB_PAT` = your fine-grained PAT
   - `SHARED_SECRET` = any random string

Note the Worker URL (e.g., `https://tasso-publish.you.workers.dev`).

## Step 2 — Create the bookmarks

In Chrome/Safari/Firefox, create two bookmarks (or just one if you skip staging).

**Publish to live:**
- Name: `Publish to tassofilm.com`
- URL: `https://tasso-publish.you.workers.dev/?key=SHARED_SECRET_VALUE&ref=main`

**Publish to preview:**
- Name: `Preview on preview.tassofilm.com`
- URL: `https://tasso-publish.you.workers.dev/?key=SHARED_SECRET_VALUE&ref=staging`

Replace `SHARED_SECRET_VALUE` with what you set in Step 1.

Drag them to the bookmarks bar so they're one click away.

## Step 3 — Test

Click the bookmark. A small confirmation page should appear ("Publishing to live site…") with a link to the target URL. Refresh that URL in ~30 seconds to see changes.

If you see an error page, the body explains what went wrong (bad key, GitHub auth, etc.).

## Notes

- **Security model:** anyone with the bookmark URL can publish. Treat the URL like a password — don't share it in chat, screenshots, or public docs. The `SHARED_SECRET` is the entire authorization story.
- **PAT rotation:** when your GitHub PAT expires (default 1 year), rerun `wrangler secret put GITHUB_PAT` with the new value. Bookmarks keep working — only the secret changes.
- **Free tier:** Cloudflare Workers free plan = 100k requests/day. You'll never come close.
- **Logs:** Cloudflare dashboard → your Worker → Logs to see every click.

## Phase 2 — Adding staging

After the basic bookmark works, the `&ref=staging` bookmark only becomes useful once you've:
1. Created a `staging` branch in the repo.
2. Modified the sync workflow to commit to the requested branch (currently it only writes to `main`).
3. Set up Cloudflare Pages with `main` → tassofilm.com and `staging` → preview.tassofilm.com.

I'll do steps 1–2 (workflow edits) when you're ready; step 3 is your Cloudflare/DNS work.
