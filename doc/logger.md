
## Log & security

Encrypting our database can be nerve-wracking! The mere thought of a leaked password and its potential consequences can
send shivers down the spine. However, to ensure optimal security, encryption is crucial. But how can we ensure that even
in case of a mishap, we can recover the old keys used?

To address this concern, we've implemented a logging system for the latest keys used. The log entries look like this:

```
[2024-03-22T10:47:54.742678+00:00] app.INFO: Creating OpenSSL-Symc key |--- Current encryptor is: openSSLSym :  :  |  [] []
[2024-03-22T10:48:01.667992+00:00] app.INFO: Creating OpenSSL-Symc key | Decrypt before ... :  :  |  [] []
[2024-03-22T10:48:03.155240+00:00] app.INFO: Creating OpenSSL-Symc key | Starting build key ... :  :  |  [] []
[2024-03-22T10:48:05.195112+00:00] app.INFO: Creating OpenSSL-Symc key | Algo: OPENSSL_KEYTYPE_RSA  :  :  |  [] []
[2024-03-22T10:48:08.434497+00:00] app.INFO: Creating OpenSSL-Symc key | Starting build key ... :  :  |  [] []
[2024-03-22T10:48:08.436697+00:00] app.INFO: Creating OpenSSL-Symc key | Starting build key ... :  :  |  [] []
[2024-03-22T10:48:08.456623+00:00] app.INFO: Deleting key: openSSL.bin :  :  |  [] []
[2024-03-22T10:48:08.462380+00:00] app.INFO: Deleting key: openSSL.key :  :  |  [] []
[2024-03-22T10:48:08.466301+00:00] app.INFO: Deleting key: openSSL_private.pem :  :  |  [] []
[2024-03-22T10:48:08.470684+00:00] app.INFO: Deleting key: openSSL_public.pem :  :  |  [] []
[2024-03-22T10:48:09.334528+00:00] app.INFO: Creating OpenSSL-Symc key |--- Done build FINISH :  :  |  [] []
```

This allows us to keep track of the different keys used, including the visible ones. Thus, even in the event of an error, there's still a slight chance of recovery. However, there's no secret about it: when we encrypt for security, we encrypt for security! We don't play hopscotch with security.