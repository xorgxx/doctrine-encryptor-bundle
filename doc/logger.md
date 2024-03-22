
## Log & security

Encrypting our database can be nerve-wracking! The mere thought of a leaked password and its potential consequences can
send shivers down the spine. However, to ensure optimal security, encryption is crucial. But how can we ensure that even
in case of a mishap, we can recover the old keys used?

To address this concern, we've implemented a logging system for the latest keys used. The log entries look like this:

```
[2024-03-22T11:18:55.757032+00:00] app.INFO: Create OpenSSL-Symc key |--- current encyptor is  : openSSLSym :  :  |  [] []
[2024-03-22T11:19:08.006861+00:00] app.INFO: Create OpenSSL-Symc key | Decrypt before ... :  :  |  [] []
[2024-03-22T11:19:09.426923+00:00] app.INFO: Create OpenSSL-Symc key |  starting build key ... :  :  |  [] []
[2024-03-22T11:19:17.533405+00:00] app.INFO: Create OpenSSL-Symc key | algo : OPENSSL_KEYTYPE_RSA  :  :  |  [] []
[2024-03-22T11:19:20.504709+00:00] app.INFO: Create OpenSSL-Symc key |  starting build key ... :  :  |  [] []
[2024-03-22T11:19:20.505388+00:00] app.INFO: Create OpenSSL-Symc key | starting build key ... :  :  |  [] []
[2024-03-22T11:19:20.535745+00:00] app.INFO: --- Delete key : CONTENT---openSSL.bin :  :  |  [] []
[2024-03-22T11:19:20.536868+00:00] app.INFO: Vg�O�������,:P<���܌R�&�L �gG���[�� � �\ƚP�E�KG����Do�p�Ib���t�V�e����밠6�����ͫ�I5����]��r>=ᩜ8s��>����.���g[Me���/_��w�W����X�i�P|�f���:�juɍ�xv�P��K3�.$�['�O��Q�����x �� ��� t@s\'3,�����=�^ZZ���/b*�o �6��t�2���r��U=�+�s*<��y`�y :  :  |  [] []
[2024-03-22T11:19:20.537379+00:00] app.INFO: -------openSSL.bin :  :  |  [] []
[2024-03-22T11:19:20.541321+00:00] app.INFO: --- Delete key : CONTENT---openSSL.key :  :  |  [] []
[2024-03-22T11:19:20.543061+00:00] app.INFO: 380e9b3f18d29e88b18977fd677c89cf9a2486b18ff87871ba7d17fbdd19e7c3 :  :  |  [] []
[2024-03-22T11:19:20.543475+00:00] app.INFO: -------openSSL.key :  :  |  [] []
[2024-03-22T11:19:20.547264+00:00] app.INFO: --- Delete key : CONTENT---openSSL_private.pem :  :  |  [] []
[2024-03-22T11:19:20.548699+00:00] app.INFO: -----BEGIN PRIVATE KEY----- MIIEvQIBADANBgkqhkiG9w0BAQEFAASCBKcwggSjAgEAAoIBAQCx4RSskG44CoJb fENH0XEI6/q5nhasfah86PmzyK8LRI7DrokzsAxD/MlPu0t9Q4N3nHwl1HxyCNQo J+Sv3jDpTLR7BzTHFoNXB7q55cGQdRe3aVGpwAbl9KXAEXlKltu18fabXgdRYEls VLejTPYq8Kb570rR2iBgSop/NP9oXRRLCswmJaiR8KmGtLMcKFX5/UaR7N/IC9Mq DRONAlqIhKH77tb9x7N/2uFnKCGdR+myzmDNsaA8Dwd3Hi80so1yrmPwMNY7paD+ DhBs5KyN2Fb0cgHSIb83PTzd7ReBcB5RxVyocZicHqjHsAuZ0cbaVoj+TkGpA+/2 agX5f08lAgMBAAECggEAIuS8YX2ToXquR7QAnr3/JnjkHjVczUl5G8e39ASSEevI 9anvUCxgu5WDCBj9nfz4dSZFVEZygwwoUhhBTB6SCbH6VYf8WnGYIiJqBr2DUwsl M919H4eD0uhC+4JyAKfHQdHNRn0TgmsY/B9CRs56G8JJfw3p53CamUkGEh4bXCVS AAR4FGuR5I32mEf8qEaaKv9GDyG7K7LIy8nWoVfogi7s7RUPwzQ59xLe6Req0fBJ Cya2otSNG9I9YYG/bIe7caCjUPQznMhBS4oQpKXDKafxHCvkJbK5VorN701MoXG6 gXskCdITbhNnDC3cACjvakmcFoPB2Ki75ouHqsBVAQKBgQDAO7WZtodxFhrZBhrB MJBkvo3L9Fwn0594O1aJPqF7FBgwoYEy1/O+cM1c3upi7SJZfJS6qxRDCRvsB3Tc jCzUht7vCsDa/cDRd4nxYt+OBu5gXNR3SD75C8ECxOND6O4c8scCP5lOYELv78H4 YJK1vvIRbT/iTHZ54DfOhMyCKQKBgQDs4nCYX0vkAacTcdB808re4WaJdmGE3qO0 AuRfElmq19cIxUfy/1tDDTREu2fh3LCTVFabRZkQaFRCEnyKShKL+Hks3AxTed/A UOxhp0iq4nOLxvz1FKlI7rVT3OqvI+5AtbOHtk3ARjx57VaTBivMs6pu9jd3K0bM mkUMy/8cnQKBgGMZJOgmAbKZm6OPkxFOsNFOrpia0zswzD/WDylXYTWqyUGgsJL+ YsglLKkYvanb515Ma/vDIAHi20MVIPhlRuMU2C5Q09TRBaq2PiVzPB3iktaYZGtN kjEfDxQXXbVvifjgaxlMogKWe0tnxQ3+8wLAedfkAw/tLRJtNTfAnBDpAoGBAI4b uhhu8wQxyZ1spfp8xDO5XN4SWqZvOh3/rikqxQgsvB/S2jr43jL3dOfW1Zc14ehA rEQyGIUzUXjhxjzf+whlY6x5xhs9/fFNmsFqrbl2aILuOUp9vqC2M6m/2jp/f6rq Vm2P/Ezg1d7/dvR0u0nubsxqgRPIroyH7+yhZMYxAoGAR+TrQBK24UOpwyEC5gA1 Ww/StnFGAKSr+eaugDvk4oC/6XnuZdGsW7SiitjNVIPYLyToBD8/l7jocTq9lmSE lUA/AbY9sonRRcQS+WenA8Tj2L41mSACvfh+l2orrxBgtP3xfgI84ImTIk0oDMe7 VHmI1lGi7dVfju+uhxSOCoE= -----END PRIVATE KEY-----  :  :  |  [] []
[2024-03-22T11:19:20.549161+00:00] app.INFO: -------openSSL_private.pem :  :  |  [] []
[2024-03-22T11:19:20.553180+00:00] app.INFO: --- Delete key : CONTENT---openSSL_public.pem :  :  |  [] []
[2024-03-22T11:19:20.554673+00:00] app.INFO: -----BEGIN PUBLIC KEY----- MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAseEUrJBuOAqCW3xDR9Fx COv6uZ4WrH2ofOj5s8ivC0SOw66JM7AMQ/zJT7tLfUODd5x8JdR8cgjUKCfkr94w 6Uy0ewc0xxaDVwe6ueXBkHUXt2lRqcAG5fSlwBF5SpbbtfH2m14HUWBJbFS3o0z2 KvCm+e9K0dogYEqKfzT/aF0USwrMJiWokfCphrSzHChV+f1GkezfyAvTKg0TjQJa iISh++7W/cezf9rhZyghnUfpss5gzbGgPA8Hdx4vNLKNcq5j8DDWO6Wg/g4QbOSs jdhW9HIB0iG/Nz083e0XgXAeUcVcqHGYnB6ox7ALmdHG2laI/k5BqQPv9moF+X9P JQIDAQAB -----END PUBLIC KEY-----  :  :  |  [] []
[2024-03-22T11:19:20.554952+00:00] app.INFO: -------openSSL_public.pem :  :  |  [] []
[2024-03-22T11:19:21.223940+00:00] app.INFO: Create OpenSSL-Symc key |--- Done build FINISH :  :  |  [] []
```

This allows us to keep track of the different keys used, including the visible ones. Thus, even in the event of an error, there's still a slight chance of recovery. However, there's no secret about it: when we encrypt for security, we encrypt for security! We don't play hopscotch with security.