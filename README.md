# Exist Integrations

Laravel application that connects disparate data sources with [Exist.io](https://exist.io).

## .env setup

To connect to the various services that use OAuth 2.0, you will need to add the following parameters to support the Client ID and Client Secret.

```php
EXIST_CLIENT_ID=""
EXIST_CLIENT_SECRET=""
```

## UI Elements

Exist Integrations uses Tailwind for its CSS.

Button usage should follow the examples on the [Buttons](https://tailwind-elements.com/docs/standard/components/buttons/) with the following rules:

- Execute an action (Update/Insert) - Colors Success (inline-block px-6 py-2.5 bg-green-500 text-white font-medium text-xs leading-tight uppercase rounded shadow-md hover:bg-green-600 hover:shadow-lg focus:bg-green-600 focus:shadow-lg focus:outline-none focus:ring-0 active:bg-green-700 active:shadow-lg transition duration-150 ease-in-out)
- Link to Manage an activity - Colors Primary (- inline-block px-6 py-2.5 bg-blue-600 text-white font-medium text-xs leading-tight uppercase rounded shadow-md hover:bg-blue-700 hover:shadow-lg focus:bg-blue-700 focus:shadow-lg focus:outline-none focus:ring-0 active:bg-blue-800 active:shadow-lg transition duration-150 ease-in-out)
- Link to Manage an activity (secondary action) - Outline Primary (inline-block px-6 py-2 border-2 border-blue-600 text-blue-600 font-medium text-xs leading-tight uppercase rounded hover:bg-black hover:bg-opacity-5 focus:outline-none focus:ring-0 transition duration-150 ease-in-out)
- Delete something - Colors Danger (inline-block px-6 py-2.5 bg-red-600 text-white font-medium text-xs leading-tight uppercase rounded shadow-md hover:bg-red-700 hover:shadow-lg focus:bg-red-700 focus:shadow-lg focus:outline-none focus:ring-0 active:bg-red-800 active:shadow-lg transition duration-150 ease-in-out)
- Correct data - Colors Warning (inline-block px-6 py-2.5 bg-yellow-500 text-white font-medium text-xs leading-tight uppercase rounded shadow-md hover:bg-yellow-600 hover:shadow-lg focus:bg-yellow-600 focus:shadow-lg focus:outline-none focus:ring-0 active:bg-yellow-700 active:shadow-lg transition duration-150 ease-in-out)

The UI framework uses vite. Any usage of new CSS elements needs to be built into the Assets folder. Execute the command:

```sh
nph run build
```
