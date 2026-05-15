#!/usr/bin/env node
import { Client } from "@notionhq/client";
import fs from "node:fs/promises";
import path from "node:path";
import { fileURLToPath } from "node:url";

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const ROOT = path.resolve(__dirname, "..");

const NOTION_TOKEN = process.env.NOTION_TOKEN;
const DATABASE_ID = process.env.NOTION_DATABASE_ID;
if (!NOTION_TOKEN || !DATABASE_ID) {
  console.error("Missing NOTION_TOKEN or NOTION_DATABASE_ID env vars");
  process.exit(1);
}

const notion = new Client({ auth: NOTION_TOKEN });

// Notion "Category" select value -> existing folder under images/
const CATEGORY_FOLDER = {
  "musical-artists": "hats",
  "wedding": "wedding",
  "street": "street",
  "studio": "fashion",
  "portraits": "portraits",
  "about": "about",
};

const MANIFEST_PATH = path.join(ROOT, "images", "manifest.json");
const STATE_PATH = path.join(ROOT, "scripts", ".sync-state.json");

function safeExt(url, fallback = "jpg") {
  try {
    const u = new URL(url);
    const m = u.pathname.match(/\.([a-zA-Z0-9]{2,4})$/);
    if (m) return m[1].toLowerCase();
  } catch {}
  return fallback;
}

async function fetchAllPages() {
  const pages = [];
  let cursor;
  do {
    const r = await notion.databases.query({
      database_id: DATABASE_ID,
      start_cursor: cursor,
      page_size: 100,
    });
    pages.push(...r.results);
    cursor = r.has_more ? r.next_cursor : undefined;
  } while (cursor);
  return pages;
}

function getProp(props, name, type) {
  const p = props[name];
  if (!p) return null;
  if (type && p.type !== type) return null;
  return p;
}
const getSelect   = (p, n) => getProp(p, n, "select")?.select?.name ?? null;
const getNumber   = (p, n) => getProp(p, n, "number")?.number ?? null;
const getCheckbox = (p, n) => getProp(p, n, "checkbox")?.checkbox ?? false;
const getFiles    = (p, n) => getProp(p, n, "files")?.files ?? [];

async function downloadFile(url, dest) {
  const res = await fetch(url);
  if (!res.ok) throw new Error(`fetch ${res.status} ${url}`);
  const buf = Buffer.from(await res.arrayBuffer());
  await fs.mkdir(path.dirname(dest), { recursive: true });
  await fs.writeFile(dest, buf);
}

async function loadJson(p, fallback) {
  try { return JSON.parse(await fs.readFile(p, "utf8")); }
  catch { return fallback; }
}

async function main() {
  const state = await loadJson(STATE_PATH, { pages: {} });
  const newState = { pages: {} };

  const pages = await fetchAllPages();
  console.log(`Fetched ${pages.length} pages from Notion`);

  const records = [];

  for (const page of pages) {
    const id = page.id;
    const last_edited = page.last_edited_time;
    const category = getSelect(page.properties, "Category");
    const order = getNumber(page.properties, "Order") ?? 9999;
    const cover = getCheckbox(page.properties, "Cover");
    const files = getFiles(page.properties, "Photo");

    if (!category) { console.warn(`Skip ${id}: no Category`); continue; }
    if (!CATEGORY_FOLDER[category]) { console.warn(`Skip ${id}: unknown Category "${category}"`); continue; }
    if (files.length === 0) { console.warn(`Skip ${id}: no Photo file`); continue; }

    const file = files[0];
    const url = file.type === "external" ? file.external.url : file.file.url;
    const ext = safeExt(url);
    const folder = CATEGORY_FOLDER[category];
    const shortId = id.replace(/-/g, "").slice(0, 24);
    const filename = `notion-${shortId}.${ext}`;
    const relPath = path.posix.join("images", folder, filename);
    const absPath = path.join(ROOT, relPath);

    const cached = state.pages[id];
    const needDownload = !cached
      || cached.last_edited !== last_edited
      || cached.relPath !== relPath;

    if (needDownload) {
      console.log(`Download ${relPath}`);
      try { await downloadFile(url, absPath); }
      catch (e) { console.error(`  failed: ${e.message}`); continue; }
    }

    newState.pages[id] = { last_edited, relPath };
    records.push({ id, category, order, cover, relPath });
  }

  records.sort((a, b) => a.order - b.order);

  const galleries = {};
  const covers = {};
  for (const cat of Object.keys(CATEGORY_FOLDER)) {
    if (cat === "about") continue;
    const list = records.filter(r => r.category === cat);
    galleries[cat] = list.map(r => r.relPath);
    const coverRec = list.find(r => r.cover) ?? list[0];
    if (coverRec) covers[cat] = coverRec.relPath;
  }
  const aboutList = records.filter(r => r.category === "about");
  const about = aboutList.length ? aboutList[0].relPath : null;

  const manifest = {
    generated_at: new Date().toISOString(),
    covers,
    galleries,
    about,
  };

  // Orphan cleanup: delete notion-* files that the previous sync created
  // but the current sync no longer references.
  const oldPaths = new Set(Object.values(state.pages).map(p => p.relPath));
  const newPaths = new Set(Object.values(newState.pages).map(p => p.relPath));
  for (const oldPath of oldPaths) {
    if (newPaths.has(oldPath)) continue;
    if (!path.basename(oldPath).startsWith("notion-")) continue;
    try {
      await fs.unlink(path.join(ROOT, oldPath));
      console.log(`Deleted orphan ${oldPath}`);
    } catch {}
  }

  await fs.writeFile(MANIFEST_PATH, JSON.stringify(manifest, null, 2) + "\n");
  await fs.writeFile(STATE_PATH, JSON.stringify(newState, null, 2) + "\n");
  console.log(`Sync complete: ${records.length} photos across ${Object.keys(galleries).filter(c => galleries[c].length).length} categories`);
}

main().catch(e => { console.error(e); process.exit(1); });
