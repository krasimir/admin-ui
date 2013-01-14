#PHP based micro administration
- - -

*Admin-UI* is very simple content management system. It's based on [Fabrico](https://github.com/krasimir/fabrico).

## Installation
There is no any special things that you should do before to use *Admin-UI*. Simply, download the files and change the settings in config.php.

## Usage
There is only one thing that you should - you should define content that you want to manage. In *Admin-UI* this content are called *resource*. To do this create a *.json* file in *resources* folder. Every resource should have its own *.json* file. For example:

    {
        "name": "news",
        "title": "News",
        "data": [
            {
                "name": "title",
                "title": "Title",
                "type": "LONGTEXT",
                "presenter": "TextBox"
            },
            {
                "name": "pop",
                "title": "AAA",
                "type": "VARCHAR(50)",
                "presenter": "Radio",
                "options": {
                    "op1": "do",
                    "op2": "maybe",
                    "op3": "no"
                }
            },
            {
                "name": "date",
                "title": "Date",
                "type": "VARCHAR(20)",
                "presenter": "DatePicker"
            },
            {
                "name": "text",
                "title": "Text",
                "type": "LONGTEXT",
                "presenter": "TinyEditor"
            },
            {
                "name": "thumbnail",
                "title": "Thumbnail",
                "type": "LONGTEXT",
                "presenter": "File"
            }
        ],
        "listing": {
            "skip": "thumbnail, text"
        }
    }

It's an object with the following syntax:

- name /required/ - it will be used in the urls and also as a name of the table in the database, so it's recommended to be lowercase, latin symbols and without spaces
- title /required/ - the name of your resource (type something meanful here)
- data /required/ - an array of objects. Every object represent a column in the database's table
    - name /required/ - the name of the field/column
    - title /required/ - it will be displayed  to the user
    - type - valid MySQL data type (for example: VARCHAR(20) or LONGTEXT)
    - presenter - could be one of the following: TextBox, TextArea, PasswordBox, DropDown, Radio, Check, File, HiddenField, TinyEditor, DatePicker
    - options: this property is required if you use DropDown, Radio and Check presenters. You can specify the values directly or use another resource. For example:
    

        "options": {
            "op1": "do",
            "op2": "maybe",
            "op3": "no"
        }

    or use another resource ([filename]:[name of a field]):

        "options": "news_categories.json:title"


## Notes
- *files* directory should be writiable