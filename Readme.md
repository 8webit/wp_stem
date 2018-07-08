# Content

* [Summary](#summary)
* [How to use](#how-to-use)
* [PosType Class](#posttype-class)
  * [create post type](#posttype::create($post_type_slug,-$options))
  * [create meta box](#posttypemeta_boxoption)
  * [add fields to post type](#postypefieldfield)
    * [single cloneable field](#add-single-cloneable-field)
    * [group cloneable field](#postypecloneable_groupgroup)
    * [add field to already created post type](#posttypeaddarg)
    * [auto complete field by slug](#autocomplete-field-by-slug)
* [Enqueue_Script Class](#enqueuescript-class)
  * [enqueue scripts or styles for user pages](#enqueuescriptenqueuefilename-src---in_footer--false-deps--array-ver--false)
  * [enqueue scripts or styles for admin pages](#enqueuescriptadmin_enqueuefilename-src---in_footer--false-deps--array-ver--false)
    * [enqueue to specific admin page](#enqueuescriptadmin_specificwp_screen_ids--array)
  * [enqueue with pattern](#enqueuescripturipattern)
* [Meta Class](#meta-class)
  * [create,delete,update post type meta value](#metasyncpost_id-meta_key-new_value---old_value--)
  * [get meta value](#metagetmeta_key-post_id-multiplytrue)

## Summary

wp_stem is lightweight package for wordpress theme/plugin developer.
With this package you can:

* enqueue scripts
* create post type or add fields to it
* modify post type meta

## How to use
add to functions.php file

``` php
use _8webit\wp_stem\Stem;

Stem::init();
```

## PostType Class

### PostType::create($post_type_slug, $options)

* __$post_type_slug__ (_string_) (__required__)

* __$options(*array*)__ (optional) - same values as  [register_post_type()](https://codex.wordpress.org/Function_Reference/register_post_type) $args parameter.

* extend_defaults(_boolean_) (optional)

creates post type.

Example of creating post type

    PostType::create('your_post_type_slug')

***

### PostType::meta_box($option)

* $options(_array_) - meta_box has same parameters as [add_meta_box()](https://developer.wordpress.org/reference/functions/add_meta_box/),but note that add_meta_box() 
has *callback* required, but in wp_stem it **isn't**.

__Function Chaining Order Is VITAL__

Example of creating meta box

    Postype::create('your_post_type_slug)->meta_box(array(//minimal paramters
        'id'        => 'your_id',   //required
        'title'    => 'your_title' // required
    ));

### Postype::field($field)

* $field
  * id (*int*) (optional) - auto generated id will be field_name_id
  * name (*string*) (**required**)
  * label (*string*) (**required**)
  * value (*string*) (optional)-  default value for field
  * type (*string*) (**required**) - HTML5 input types, textarea, __wp_media__, __color(spectrum color picker)__ or __font(google fonts picker)__.
  * cloneable (*boolean*) (optional)
  * **Any HTML Attribute**

adds field to post type.function chaining is important here.
must used after Postype::add() or Postype::create() function.see example below.

__Function Chaining Order Is VITAL__


**Note: to add field to post type you *must* create *meta box* first.to create meta box use meta_box() function.**

finally add field

    PostType::create('your_post_type_slug')-> meta_box(array(
        'id'        => 'your_id',
        'title'    => 'your_title'
    ))->field(array(
        'id'        => 'your_field_id',
        'name'      => 'your_field_name',
        'label'     => 'your field label',
        'value'     => 'your_field_default_value',
        'type'      => 'your_field_type'
        'cloneable' => true
    ));

**Note**: when $field **'type'** parameter equals to 'color', spectrum color picker will be used(not html5 color picker).

## Add Single Cloneable Field

to add single cloneable field use [Postype::field()](#postype-field-$field-) and pass 'cloneable' => true ,as shown in example

### Postype::cloneable_group($group)

* $group (array)
  * group_id (int)(__required__) - __id of the field group.used for retrieve field meta values__
  * fields (array)(__required__) - same arguments as [Postype::field()](#postype-field-$field-)
  * options (array)(_optional_)
    * title (string)  - title of the cloneable meta box
    * add_button (string) - add (clone) button label
    * remove_button (string) - remove (clone) button label

Adds group cloneable field to meta box

Example of adding group cloneable field

    PostType::create('your_post_type_slug')-> meta_box(array(
            'id'        => 'your_id',
            'title'    => 'your_title'
        ))->cloneable_group(array(
            'your_group_cloneable_id',
            'fields' => array($field1,$field2,$field3 ...),
            'options => array(
                'title' => 'title of meta box group',
                'add_button' => 'add button label',
                'remove_button' => 'remove button label'
            )

        ));

### PostType:add(_$arg_)

* __$arg__ (int | string) - post type id, post type slug or template name ('template-example.php')

 adds field by (Already Created) post type,post id or page template

for example,lets add field to post:

    PostType::add('post')-> meta_box(array(
        'id'        => 'your_id',
        'title'    => 'your_title'
    ))->field(array(
        'id'        => 'your_field_id',            // required
        'name'      => 'your_field_name',          // required
        'label'     => 'your_field_label',         // required
        'value'     => 'your_field_default_value', // required
        'type'      => 'your_field_type'           // required
        'cloneable' => 'is_cloneable_field'        // optional
    ));

## Autocomplete field by slug

for autcomplete action jquery autocomplete is used.

What to autocomplete happens based on html class,so if you want to field was autcompleted by users slug  add 'class'=> 'autocomplete-user' as shown in example.another class is 'autocomplete-page'

Example of users autocomplition

     Postype->field(array(
            'name'  => 'author_search',
            'label' => 'Select Author',
            'type'  => 'text',
            'placeholder' => 'Search Author By Username...',
            'class' => 'autocomplete-user'
            ));

## Meta Class

### Meta::sync($post_id, $meta_key, $new_value = "", $old_value = "")

* $post_id (*int*) (**required**)
* $meta_key (*string*) (**required**)
* $new_value (*string*) (optional)
* $old_value (*string*) (optional)

create,delete or update post type with one function

example of updating value:

    $old_value = Meta::get('lorem_ipsum');
    $new_value = $_POST['lorem_ipsum'];

    Meta::sync(get_the_ID(), 'lorem_ipsum', $new_value, $old_value);

### Meta::get($meta_key, $post_id='', $multiply=true)

* $meta_key (*string*) (**required**)
* $meta_key (*int*) (optional)
* $meta_key (*int*) (optional)

get post meta without pass post id.if $post_id not passed get_the_ID()
function will be used to retrieve current id

## Enqueue Script Class

Enqueue_Script Class Responsible for load scripts and style in user or admin pages.

script should locate in $your_theme_dir/assets/js

styles should locate in $your_theme_dir/assets/css

### Enqueue_Script::enqueue($filename, $src = '', $in_footer = false, $deps = array(), $ver = false)

* $filename (*string*) (**required**) - filename with its extension.example: _myscript.js_ (not just _myscript_)
* $src (_string_) (_optional_) - path relative to theme folder. if not provided default file structure pattern("assets/[file_type]/[file_name].[file_type]") will be used.
* $in_footer (_Bollean_) (_optional_)
* $deps (_array_) (_optional_) - filenames of dependecies
* $ver (_array_) (_optional_)

Enqueue script or style by filename with its extension for **user** pages.
if src not provided default file structure pattern("assets/[file_type]/[file_name].[file_type]") will be used.
uses same parameters as  [wp_enqueue_script()](https://developer.wordpress.org/reference/functions/wp_enqueue_script/) or [wp_enqueue_style()](https://developer.wordpress.org/reference/functions/wp_enqueue_style/).

Example:

file structure

```
mytheme
|---enqueuescripts
|   |---js
|   |   |   myscript.js
|   |
|   |---css
|       |   mystyle.css
|
|---acme_vendor_script
|   |---lorem_ipsum
|   |   |   vendor.js

*  *  *  *

|--- other folder and files
```

```
    Enqueue_Script::enqueue('myscript.js');
    Enqueue_Script::enqueue('mystyle.css');

    Enqueue_Script::enqueue('vendor.js', 'acme_vendor_script/lorem_ispum');
```

### Enqueue_Script::admin_enqueue($filename, $src = '', $in_footer = false, $deps = array(), $ver = false)

for parameters see [Enqueue_Script::enqueue()](#enqueuescriptenqueue)

loads scripts in **admin** pages

### Enqueue_Script::admin_specific($wp_screen_ids = array())

* $wp_screen_ids (_array_) (**required**)

**Note**: only works when used in chaining or will be hooked to last enqueud script

admin_specifc only loads enqueued scripts  for admin in given view

Example:

    Admin::enqueue('sciprt.js')->admin_specific(array(
                                    'first_admin_screen_id',
                                    'second_admin_screen_id',
                                ));
    // Or

     Admin::enqueue('sciprt.js');
     Enqueue_Script::admin_specific(array(
                            'first_admin_screen_id',
                            'second_admin_screen_id'
                            );

### Enqueue_Script::uri($pattern)

* $pattern (_string_) (**required**) - theme root relative file pattern. see [glob()](http://php.net/manual/en/function.glob.php)

returns single or multiplay full file path with given pattern. Useful when you need load hashed script or style

Example:

file structure

```
mytheme
|---acme_vendor_script
|   |---lorem_ipsum
|   |   |   as8du9asud982381.bundle.js

*  *  *  *

|--- other folder and files
```

```
$uri = Enqueue_Script::uri('acme_vendor_script/lorem_ipsum/*.bundle.js');

echo '<script type="text/javascript" src="'. $uri .'"></script>';
```


***

wp_stem 1.0.0

***
Copyright (C) 2018  [8webit.com](https://8webit.com)

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see [licenses](http://www.gnu.org/licenses).
