# Add translations of nodes in another language.

The add translation module creates translated nodes in bulk for a language, instead of manually translating each individual node.

## Installation Steps

1. Download the folder `add_translation` and place it under modules/custom directory.
2. Enable the module `add_translation` from Extends.
3. It will automatically enable the dependent modules: Language, Locale, and Content Translation.
4. Go to the language page and add the required `languages` (/admin/config/regional/language)
5. Go to the `Content language and translation page` (/admin/config/regional/content-language) and select the content types that you want to enable translation for.
6. Once you have enabled translation for the content types and added the translation languages, you can start configure this module and click the `Add Translation` button to add the translation.

## How to confgure the Module
1. Go to the configuration -> `Configure Bulk Translation` (/admin/config/add-translation/settings)
2. Choose the `language`, `content type` and `status` from the dropdown and save the configuration.

![Screenshot](screenshot.png)

3. Finally click the button `Add Translation` from Bulk Translation Tab. It will generate the translated nodes in bulk for the selected language and content type.

![Screenshot](screenshot-add.png)

4. It runs in batches to translate the nodes.
