# Git Pull Instructions

When running `git fetch` / `git pull` in this repository (`eash-laravel`), always run these commands immediately after a successful `git pull`:

```bash
sudo chown -R www-data:www-data .
sudo chmod -R 775 .
```

Scope:

- Apply this only in the `eash-laravel` repository.
- Do not apply this automatically in other repositories.
