# Laravel Auth Boilerplate

A boilerplate for Laravel projects based on [github.com/laravel-api-boilerplate](https://github.com/specialtactics/laravel-api-boilerplate) 
with a focus on advanced authentication, authorization, and API policies.
Contains a dedicated entrypoint for Admin panel based on [core-ui](https://coreui.io/) admin template.

## Installation
You can quickly get the environment up and running by
```bash
./env/build.sh

```

You can also use native docker support which quickly boots up the environment via
```
docker-compose up
```

Notes:
Make sure to create an `.env` file from `.env.example` to change runtime configuration

## API

To select appropriate version of API, following header must be provided (`v1` defines the endpoint version):
```bash
Accept: application/vnd.laravelauth.v1+json
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

## Routes

Complete list of supported API is given in table below. Click to view fully expanded table.
<details><summary>Click to view</summary>
<p>

#### Routes list

 Method   | URI                                | Action                                      | Protected 
----------|------------------------------------|---------------------------------------------|-----------
 GET,HEAD | api/auth/login                     | AuthController@token                        | No        
 POST     | api/auth/register                  | AuthController@register                     | No        
 GET,HEAD | api/auth/oauth/{provider}          | AuthController@redirectToProvider           | No        
 POST     | api/auth/oauth/callback/{provider} | AuthController@handleProviderCallback       | No        
 POST     | api/auth/password/email            | ForgotPasswordController@sendResetLinkEmail | No        
 POST     | api/auth/password/reset            | ResetPasswordController@reset               | No        
 GET,HEAD | api/auth/email/verify/{id}/{hash}  | VerificationController@verify               | No        
 POST     | api/auth/email/resend              | VerificationController@resend               | No        
 GET,HEAD | api/auth/me                        | AuthController@getUser                      | Yes       
 DELETE   | api/auth/logout                    | AuthController@logout                       | Yes       
 GET,HEAD | api/auth/token/refresh             | AuthController@refresh                      | Yes       
 GET,HEAD | api/user                           | UserController@getAll                       | Yes       
 GET,HEAD | api/user/{id}                      | UserController@get                          | Yes       
 POST     | api/user                           | UserController@post                         | Yes       
 PUT      | api/user/{id}                      | UserController@put                          | Yes       
 PATCH    | api/user/{id}                      | UserController@patch                        | Yes       
 DELETE   | api/user/{id}                      | UserController@delete                       | Yes       
 GET,HEAD | api/user_status                    | UserStatusController@getAll                 | Yes       
 GET,HEAD | api/user_status/{id}               | UserStatusController@get                    | Yes       
 GET,HEAD | api/role                           | RoleController@getAll                       | Yes       
 GET,HEAD | api/role/{id}                      | RoleController@get                          | Yes       
 GET,HEAD | api/user_role                      | UserRoleController@getAll                   | Yes       
 GET,HEAD | api/user_role/{id}                 | UserRoleController@get                      | Yes       
 POST     | api/user_role                      | UserRoleController@post                     | Yes       
 PUT      | api/user_role/{id}                 | UserRoleController@put                      | Yes       
 PATCH    | api/user_role/{id}                 | UserRoleController@patch                    | Yes       
 DELETE   | api/user_role/{id}                 | UserRoleController@delete                   | Yes       
 GET,HEAD | api/user_log                       | UserLogsController@getAll                   | Yes       
 GET,HEAD | api/user_log/{uuid}                | UserLogsController@get                      | Yes       
 GET,HEAD | api/referral                       | ReferralController@getAll                   | Yes       
 GET,HEAD | api/referral/{id}                  | ReferralController@get                      | Yes       

</p>
</details>
