
Gemini TTS

```
./google-cloud-sdk/bin/gcloud auth application-default login
```

Your browser has been opened to visit:
...

Credentials saved to file: [/Users/ivanpirogov/.config/gcloud/application_default_credentials.json]

These credentials will be used by any library that requests Application Default Credentials (ADC).
WARNING:
Cannot find a quota project to add to ADC. You might receive a "quota exceeded" or "API not enabled" error. Run $ gcloud auth application-default set-quota-project to add a quota project.

добавляем креды (https://cloud.google.com/docs/authentication/adc-troubleshooting/user-creds)

```
/Users/ivanpirogov/Downloads/google-cloud-sdk/bin/gcloud auth application-default set-quota-project default
```

чисто конф
❯ /Users/ivanpirogov/Downloads/google-cloud-sdk/bin/gcloud config list

❯ /Users/ivanpirogov/Downloads/google-cloud-sdk/bin/gcloud config set project panki-markdown

пишет

>WARNING: Your active project does not match the quota project in your local Application Default Credentials file. This might result in unexpected quota issues.
>To update your Application Default Credentials quota project, use the `gcloud auth application-default set-quota-project` command.
WARNING: [waukaleuka@gmail.com] does not have permission to access projects instance [panki-markdown] (or it may not exist): The caller does not have permission. This command is authenticated as waukaleuka@gmail.com which is the active account specified by the [core/account] property
Are you sure you wish to set property [core/project] to panki-markdown?

❯ /Users/ivanpirogov/Downloads/google-cloud-sdk/bin/gcloud auth application-default set-quota-project
>ERROR: (gcloud.auth.application-default.set-quota-project) Cannot add the project "panki-markdown" to application default credentials (ADC) as a quota project because the account in ADC does not have the "serviceusage.services.use" permission on this project.

проверить проекты:
```shell
/Users/ivanpirogov/Downloads/google-cloud-sdk/bin/gcloud projects list
```

создаем проект:
```shell
/Users/ivanpirogov/Downloads/google-cloud-sdk/bin/gcloud projects create "panki-markdown" --name="Panki TTS Project"
```


