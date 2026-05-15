// Cloudflare Worker — Tasso publish trigger
//
// Hit with a GET request from a bookmark to fire the GitHub Action that
// syncs photos from Notion. Holds the GitHub PAT as a Worker secret so the
// bookmark URL only contains a shared key, not the PAT itself.
//
// Bookmark URL:
//   https://<your-worker>.workers.dev/?key=SHARED_SECRET            (publishes to main → tassofilm.com)
//   https://<your-worker>.workers.dev/?key=SHARED_SECRET&ref=staging (publishes to staging → preview.tassofilm.com)
//
// Required Worker secrets (set via `wrangler secret put` or the Cloudflare dashboard):
//   GITHUB_PAT     — fine-grained PAT scoped to japper2277/TASSO with Actions: Read and write
//   SHARED_SECRET  — random string the bookmark URL carries to prove it's authorized

const REPO = "japper2277/TASSO";
const WORKFLOW = "sync-from-notion.yml";

const SITE_URLS = {
  main: "https://tassofilm.com",
  staging: "https://preview.tassofilm.com",
};

export default {
  async fetch(request, env) {
    const url = new URL(request.url);
    const key = url.searchParams.get("key");
    const ref = url.searchParams.get("ref") || "main";

    if (!env.SHARED_SECRET || key !== env.SHARED_SECRET) {
      return html(401, "Unauthorized", "Bookmark is missing or has the wrong <code>key</code>.");
    }
    if (ref !== "main" && ref !== "staging") {
      return html(400, "Bad ref", `Unknown <code>ref</code>: ${escape(ref)}. Use <code>main</code> or <code>staging</code>.`);
    }

    const ghRes = await fetch(
      `https://api.github.com/repos/${REPO}/actions/workflows/${WORKFLOW}/dispatches`,
      {
        method: "POST",
        headers: {
          "Accept": "application/vnd.github+json",
          "Authorization": `Bearer ${env.GITHUB_PAT}`,
          "X-GitHub-Api-Version": "2022-11-28",
          "User-Agent": "tasso-publish-worker",
          "Content-Type": "application/json",
        },
        body: JSON.stringify({ ref }),
      },
    );

    if (ghRes.status === 204) {
      const target = SITE_URLS[ref];
      return html(
        200,
        ref === "staging" ? "Publishing preview…" : "Publishing to live site…",
        `GitHub Action started. The site will update in ~30 seconds to 2 minutes.<br><br>
         <a href="${target}" target="_blank">Open ${target}</a> and refresh in a moment.`,
      );
    }

    const body = await ghRes.text();
    return html(ghRes.status, "GitHub error", `<pre>${escape(body)}</pre>`);
  },
};

function html(status, title, body) {
  return new Response(
    `<!doctype html><meta charset="utf-8"><title>${title}</title>
     <style>body{font:16px/1.5 -apple-system,system-ui,sans-serif;max-width:520px;margin:40px auto;padding:0 16px;color:#222}h1{font-size:20px}a{color:#0a66c2}</style>
     <h1>${title}</h1><p>${body}</p>`,
    { status, headers: { "Content-Type": "text/html; charset=utf-8" } },
  );
}

function escape(s) {
  return String(s).replace(/[&<>]/g, (c) => ({ "&": "&amp;", "<": "&lt;", ">": "&gt;" }[c]));
}
