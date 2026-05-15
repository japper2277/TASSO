// Cloudflare Pages middleware — gates the staging deploy behind Basic Auth.
//
// Runs before any other request handling on every Pages request. On the
// `main` branch (production = tassofilm.com) it lets everything through.
// On any non-main branch (staging = preview.tassofilm.com) it requires
// Basic Auth using PREVIEW_USER / PREVIEW_PASS environment variables set
// in the Cloudflare Pages dashboard.
//
// Required environment variables (Cloudflare Pages → Settings → Environment
// Variables, with "Preview" environment selected — leave Production blank):
//   PREVIEW_USER  — the username the client types in the browser prompt
//   PREVIEW_PASS  — the password they type
//
// Browsers cache Basic Auth credentials for the session, so the client only
// sees the prompt the first time on a given device/browser.

export async function onRequest(context) {
  const { request, env, next } = context;

  if (env.CF_PAGES_BRANCH === "main") {
    return next();
  }

  if (!env.PREVIEW_USER || !env.PREVIEW_PASS) {
    return next();
  }

  const auth = request.headers.get("Authorization");
  const expected = "Basic " + btoa(`${env.PREVIEW_USER}:${env.PREVIEW_PASS}`);

  if (auth === expected) {
    return next();
  }

  return new Response("Authentication required", {
    status: 401,
    headers: {
      "WWW-Authenticate": 'Basic realm="Tasso Preview", charset="UTF-8"',
      "Content-Type": "text/plain; charset=utf-8",
    },
  });
}
