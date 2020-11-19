<p align="center"><img src="https://laravel.com/assets/img/components/logo-laravel.svg"></p>

> This serves as **backend API** for Jourfixer project. 
It will also have a dedicated entrypoint for Admin panel based on [core-ui](https://coreui.io/) admin template.

### Laravel API Boilerplate
Find the boilerplate used for this project at [github.com/laravel-api-boilerplate](https://github.com/specialtactics/laravel-api-boilerplate).

This has been heavily extend and upgraded.

---

## API

To select appropriate version of API, following header must be provided (`v1` defines the endpoint version):
```bash
Accept: application/vnd.jourfixer.v1+json
```
Currently supported versions: `v1`

### Request
Each endpoint supports following predefined request options by default:
* **sorting** - `/model?sort=-name`
* **filtering** - `/model?filter[name]=john&filter[email]=gmail`
* **selecting** - `/model?fields[users]=id,name`
* **eager loading** - `/model?include=posts`
* **custom appends** - `/model?append=fullname`

For each of these items, advanced authorization policies are available (both per request, and per request item). 

For example, if we wish to allow preview of user `logs` only for administrator:
```php
// This disables possiblity of requesting "logs"
// for anyone except admin users.
//
// Workflow:
//   a) regular user requests `/user?include=logs` -> HTTP Unauthorized
//   b) regular user requests `/user` -> gets default user response
//   c) admin user requests `/user?include=logs` -> response with logs lazy loaded
//   d) admin user requests `/user` -> gets default user response
public function includeLogs(User $loggedUser, User $user)
{
    return $loggedUser->isAdmin();
}
```

Natively, every restful model supports **sorting, filtering**, and **selecting** of all fields user has permissions to see.
This is internally handled via view policies. Likewise, these options can be extend to support custom logic. To do so, simply override RestfulModel `getQuery` methods.

### Response
In addition to requests, on top of model objects there is per-attribute authorization implemented as well.
By implementing specific policies, it is possible to extend model attribute view and edit permissions. 

For example, consider following response:
```json
{
    "id": 1,
    "name": "ramiz",
    "is_banned": false,
    "phone_number": "123",
    "role": 1
}
```
It is possible to forbid users from obtaining details of specific attributes by defining **View policies**.
```php
// This hides "phone_number" from response
// for anyone except the owner of the account.
public function viewPhoneNumber(User $loggedUser, User $user)
{
    return $loggedUser->id == $user->id;
}
```

Also, there are fields which only specific users can edit. To implement edit field permissions, simply write related **Edit policies**.
```php
// This allows configuration of "is_banned"
// to administrators.
public function editIsBanned(User $loggedUser, User $user)
{
    return $loggedUser->isAdmin();
}
```

**Note:** By default, all fields that are marked as hidden will remain hidden unless related policies are defined. The same applies to editable fields (editable unless a condition is given).

#### Eager loading
As some models require eager loading enabled by default, there is also an option for that by overriding model `getWith` method. For example, if we wish to enable `role` (which is a one-to-one relation) on `user` to be displayed along user, we can simply give it as:
```php
public function getWith() {
    return ['role_status'];
}
```

Now, when requesting default endpoint at `/user/1` response will be given as:
```json
{
    "id": 1,
    "name": "ramiz",
    "is_banned": false,
    "phone_number": "123",
    "role": {
        "id": 1,
        "name": "admin"
    }
}
```

Authorization policies are also enabled, which will hide these fields in case of insufficient permissions. Example usage include providing user logs for owner account.
