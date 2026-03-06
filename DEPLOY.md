# Deployment Guide

## Netlify

Deploy to production:
```bash
npx netlify deploy --prod
```

First time setup (if not linked):
```bash
npx netlify link --name christophertasso
```

Live URL: https://christophertasso.netlify.app

---

## GitHub Pages

Commit and push changes:
```bash
git add .
git commit -m "Your commit message"
git push
```

GitHub Pages automatically rebuilds after each push (takes ~1 minute).

Live URL: https://japper2277.github.io/TASSO/

Repo: https://github.com/japper2277/TASSO
