# Render deployment

GradConn runs on Render as a Docker web service. Aiven supplies MySQL, a private
S3-compatible bucket stores uploads, and Brevo sends mail through its HTTPS API.

## Prepare the external services

Create an Aiven MySQL service and download its CA certificate. Create a private
S3-compatible bucket and obtain its S3 endpoint, region, access key, secret key,
and bucket name. In Brevo, validate the sender address and create an API key.

Convert the Aiven certificate into a single-line value for Render:

```powershell
[Convert]::ToBase64String([IO.File]::ReadAllBytes("ca.pem"))
```

On Linux or macOS, use `base64 -w 0 ca.pem`.

## Create the Render service

Push the repository to GitHub. In Render, choose **New > Blueprint**, connect the
repository, and select `render.yaml`. Supply every variable marked `sync: false`.
Use the public Render URL, including `https://`, for `APP_URL`.
Generate `APP_KEY` locally with `php artisan key:generate --show` and paste the
complete `base64:` value into Render.

Copy the Aiven connection values into `DB_HOST`, `DB_PORT`, `DB_DATABASE`,
`DB_USERNAME`, and `DB_PASSWORD`. Paste the base64 certificate into
`AIVEN_CA_BASE64`; startup writes it to `MYSQL_ATTR_SSL_CA`.

For private uploads, set `AWS_ACCESS_KEY_ID`, `AWS_SECRET_ACCESS_KEY`,
`AWS_DEFAULT_REGION`, `AWS_BUCKET`, and `AWS_ENDPOINT`. Keep
`PRIVATE_UPLOADS_DISK=s3` and keep the bucket private. GradConn checks access before
streaming each file.

For Brevo, set `BREVO_API_KEY`, `MAIL_FROM_ADDRESS`, and `MAIL_FROM_NAME`. The from
address must be authenticated in the same Brevo account.

## Verify the deployment

The container builds frontend assets, installs production packages, decodes the
Aiven certificate, migrates the database, caches Laravel configuration, and starts
Apache on Render's assigned port. It also runs the database-backed Laravel queue
worker used for email delivery. It does not seed users automatically.

Production uses encrypted cookie sessions to avoid a database query on every
request. Existing users may need to sign in once after the first deployment that
switches from database sessions.

In Render Shell, run:

```bash
php artisan gradconn:check --database --mail
php artisan gradconn:test-mail your-address@gmail.com
```

To create the administrator without a Render Shell, temporarily supply the
`ADMIN_SEED_*` environment variables and deploy. Startup runs `AdminSeeder` when
`ADMIN_SEED_PASSWORD` is present. Remove the password from the environment after
the account has been created so later deployments cannot reset it.

To create the single Alumni Officer, temporarily set the four
`ALUMNI_OFFICER_SEED_*` variables and deploy. Startup runs
`AlumniOfficerSeeder` when `ALUMNI_OFFICER_SEED_PASSWORD` is present. After the
successful deployment, remove that password variable. The same seeder updates
the existing officer if credentials must be reset; it never adds a second
Alumni Officer.

## Troubleshooting

- Database TLS errors usually mean the base64 CA value is incomplete.
- Uploads lost after a restart mean `PRIVATE_UPLOADS_DISK` is still `local`.
- Brevo rejections mean the sender is unauthenticated or belongs to another account.
- For an initial 500, inspect Render Logs and check `APP_KEY`, database variables,
  and migration output.
- A free Render service can sleep while idle, so its first request may be slower.
- If email remains pending, inspect the Render logs for the queue worker and run
  `php artisan queue:failed` in Render Shell.
