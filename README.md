#PHP based micro content management system
- - -

*Admin-UI* is very simple content management system. It's based on [Fabrico](https://github.com/krasimir/fabrico) and doesn't require any database management. All you have to do is to define your content objects and relation.

## Installation

1. Download the files 
2. Create */config/config.php* file (use */config/example.config.php* as a reference)

## Usage
There is only one thing that you should do - describe your content. In the context of *Admin-UI* this means to add your resources. To do this create a *.json* file in *resources* folder. Every resource should have its own *.json* file. For example:

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
    - type /required/ - valid MySQL data type (for example: VARCHAR(20) or LONGTEXT)
    - presenter /required/ - could be one of the following: TextBox, TextArea, PasswordBox, DropDown, Radio, Check, File, Image, HiddenField, TinyEditor, DatePicker
    - options: this property is required if you use DropDown, Radio and Check presenters. You can specify the values directly or use another resource. 

        For example:

            "options": {
                "op1": "do",
                "op2": "maybe",
                "op3": "no"
            }

        or use another resource ([filename]:[name of a field]):

            "options": "news_categories.json:title"

    - validation /optional/ - there are several validators available: NotEmpty, LengthMoreThen, LengthLessThen, ValidEmail, Match, Not, MoreThen, LessThen, Int, Float, String. Separate the validators with commas. 

        For example:

        "validation": "NotEmpty, Int, LengthMoreThen/4"

    The user should type something that is an integer and to have four symbols. As you may guess, some of the validators require parameter to be sent. You can do that by using */* as it is done in the example above.
- listing /optional/ - it's an object with property *skip*, where you can specify the fields, which should be hidden in the list view

    For example

        "listing": {
            "skip": "thumbnail, text"
        }

- parent /optional/ - you are able to create resource, which has children. In other words if some of your resources has to be nested for some of the others you should define *parent*. 

    For example:

        "parent": "news.json"

Your nested resources will not be visible in the landing page, but will appear as buttons in the parent resource list page.

## Manage images
There is *Image* presenter, which deals with uploading pictures. *Admin-ui* gives you ability to control not only the uploaded file, but also to generate different sizes based on the original. Just change the settngs in your *config.php* file:

    global $IMAGE_SIZES;
    $IMAGE_SIZES = array(
        (object) array("prefix" => "small_", "height" => 100),
        (object) array("prefix" => "small2_", "width" => 100),
        (object) array("prefix" => "exact_", "width" => 100, "height" => 100),
        (object) array("prefix" => "scale_", "scale" => 30)
    ); 

## Notes
- *files* directory should be writiable
