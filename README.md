# Lokalize

Manage translation strings for your app in Cockpit



![image](https://user-images.githubusercontent.com/321047/52646355-e4f10c80-2ee2-11e9-87ff-6aed53f23b73.png)


# Configuration

Configuration `config/config.yaml`:

```
lokalize:
    importkeys: false,
    publicAccess: false,
    delimiter: ";",
    translationService:
        provider: Google // or Yandex
        apikey: AIzaxxx

```

# Api

Get localized strings of a project:

```
GET /api/lokalize/project/{name}?token=*apitoken*
```

Get strings for a specific language

```
GET /api/lokalize/project/{name}/{local}?token=*apitoken*
```

Update localized strings of a project:

```
PATCH /api/lokalize/project/{name}?token=*apitoken*
```

Update strings for a specific language

```
PATCH /api/lokalize/project/{name}/{local}?token=*apitoken*
```

### 💐 SPONSORED BY

[![ginetta](https://user-images.githubusercontent.com/321047/29219315-f1594924-7eb7-11e7-9d58-4dcf3f0ad6d6.png)](https://www.ginetta.net)<br>
We create websites and apps that click with users.
