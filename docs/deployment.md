# Deploying to the Nextcloud app store

## Prerequisites

- Copy your app certificate files to `./docker/nextcloud/certificates`

### Signing and releasing

- Make sure the version in `appinfo/info.xml` and the `CHANGELOG.md` are updated
- Sign the app with `cd docker && make sign-app`
    - You should now have a `nextbackup-nc.tar.gz` in your git directory
    - Check the content of the archive for unwanted files (you can exclude more files in
      `docker/nextcloud/sign-app.sh`)
- Create a new release on [NextBackup releases](https://github.com/pbek/nextbackup/releases)
  with the version like `20.9.0` as *Tag name* and *Release title* and the changelog text of the current
  release as *Release notes*
    - You also need to upload `nextbackup-nc.tar.gz` to the release and get its url
      like `https://github.com/pbek/nextbackup/releases/download/20.9.0/nextbackup-nc.tar.gz`
- Take the text from *Signature for your app archive*, which was printed by the sign-app command and
  release the app at [Upload app release](https://apps.nextcloud.com/developer/apps/releases/new)
    - You need the download link to `nextbackup-nc.tar.gz` from the GitHub release
- The new version should then appear on the [NextBackup store page](https://apps.nextcloud.com/apps/nextbackup)
