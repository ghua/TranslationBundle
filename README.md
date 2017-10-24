Overview
========

This bundle handles translations. Though there are several other Symfony bundles 
that do the same, this one is algorithm-agnostic and ORM-agnostic. Translations are stored
in entities that might be the same as Doctrine entities, but technically any other ORM 
can be used as well as non-DB persistence solutions. Users can use default translation
algorithms shipped with the bundle or create some on their own. An algorithm can use
one or more drivers. Three drivers are shipped with this bundle - one uses Doctrine and
and is written for situation where a translations table exists per every translatable
entity, the second uses Google Translate, while the third allows to use another field of
translated entity as a fallback.

Installation
============

There are several steps that need to be done before using the bundle.

1) Create these entries in ```config.yml```
```
vkr_translation:
    language_entity: "MyBundle:MyLanguageEntity"
    locale_retriever_service: "my.locale_retriever"
```

2) Create a locale retriever service. It must implement ```LocaleRetrieverInterface```
and define its two methods. Both methods can return ISO language codes in either short
(as in 'en') or long (as in 'en_US') forms.

3) Define a language entity. It must implement ```LanguageEntityInterface```. The ```getCode()```
method should return a language code in the same form as returned by your locale retriever.

4) Define at least one pair of entities - see below.

5) (Optional) If you want to use Google Translations integration, set ```vkr_translation.google_api_key``` parameter
in ```parameters.yml```.

Defining entity pairs
=====================

This bundle is built around the idea of having a separate translation entity for every entity that
needs to be translated. Therefore, two entities are needed - a **translatable entity** that is to be translated and a 
**translation entity** that contains translations. 

The translatable entity must implement ```TranslatableEntityInterface```, while its translation entity
must implement ```TranslationEntityInterface```. Also, there is a naming convention:
the translation entity full class name must be the same as that of translatable entity (including namespace)
with 'Translations' suffix.

It is recommended (though not necessary) to use provided traits for both interfaces:
```TranslatableEntityTrait``` and ```TranslationEntityTrait```. The second one provides
all needed methods for translation entity, but translatable entities requires one more method to
be defined manually - ```getTranslatableFields```, which should return an array of field names
for every field of translation entity, excluding primary key and associations. For every field that
is returned, a getter and a setter need to be defined manually on the translatable entity, their names
should be identical to those on the translated entity. In other words, if you have
a 'name' field on the translation entity, you need to write something like this on
the translatable entity:

```
public function getTranslatableFields()
{
    return ['name'];
}

private $name;

public function setName($name)
{
    $this->name = $name;
    return $this;
}

public function getName()
{
    return $this->name;
}
```

If you are using Doctrine, YAML or XML mappings for both entities should contain these:

1) PK fields called "id".

2) One-to-many association to "translations" on translatable entity (eager loading
highly recommended).

3) Many-to-one associations on translation entity to "language" and "entity" that point
to language entity and translatable entity, respectfully.

Examples for YAML mappings of language, translatable and translation entities are provided
in ```Examples``` folder.

Optional methods on translatable entity
======================================

A translatable entity may define two more methods.
 
```getTranslationFallback```. Under default algorithm, it allows to define a scalar value or another field on the
translatable entity as a fallback in case no translations are found. It accepts one optional
argument, ```$field```, which can be used in case there is more than one translatable field.
Under default algorithm, if ```false``` is returned, the bundle will revert to default behavior, that is, throw a
```TranslationException```. If you do not want the exception to be thrown, but just return an empty
string if translations are not found, just make ```getTranslationFallback``` return empty string.

This example will return slug instead of name and 'foo' instead of description:

```
public function getTranslationFallback($field = '')
{
    if ($field == 'name') {
        return $this->getSlug();
    }
    if ($field == 'description) {
        return 'foo';
    }
    return '';
}
```

If you want to use this method in your custom algorithm, you must include a dependency
on ```EntityTranslationDriver```.

```isGoogleTranslatable``` method should return true if you want to enable Google
Translations for the entity. If not present, Google Translations will be disabled under default algorithm.

If you want to use this method in your custom algorithm, you must include a dependency
on ```GoogleTranslationDriver```.

TranslationManager::translate() method
======================================

The most basic usage of this bundle is as follows:

```
$translationManager = $this->get('vkr_translation.translation_manager');
$translatedEntity = $translationManager->translate($entity);
```

It will return the first argument (the translatable entity), but the translatable fields
will now be populated and accessible via getters. Note that cloning is not used, and the original
```$entity``` will be changed.

The first argument to ```translate()``` can be changed to an array of translatable entities,
as returned by Doctrine's ```findBy()``` and ```findAll()```. In this case, the same array of
entities will be returned.

There are two more optional arguments. 

The second argument is the locale string in the same format that is returned by language 
entity's ```getCode()```. If not specified, the target language will be determined by 
```getCurrentLocale()``` of your locale retriever service.

The third argument is the ordering column that should be the name of one of entity's translatable fields. 
If the first argument is an array, its elements will be reordered based on that field.
Currently only ascending ordering is supported.

Because exceptions can be thrown, it is recommended to catch ```TranslationException```
on every call to ```translate()```.

Algorithms and drivers
======================

This bundle can work with multiple translation algorithms. By default, ```DefaultAlgorithm``` is used.
You can swap algorithms by using this syntax:

```
$algorithm = $this->get('my.algorithm');
$translationManager->setAlgorithm($algorithm);
```

You can create your own algorithm by implementing ```VKR\TranslationBundle\Interfaces\TranslationAlgorithmInterface```.
It must contain ```getTranslation()``` method which must return a translation entity.
While technically all external DB and API calls can be contained in the algorithm 
itself, it is recommended to decouple them into driver classes. A driver is a plain
PHP class that handles external calls. Three drivers are shipped with the bundle:
```VKR\TranslationBundle\Services\DoctrineTranslationDriver```,
```VKR\TranslationBundle\Services\GoogleTranslationDriver``` and 
```VKR\TranslationBundle\Services\EntityTranslationDriver```. The third one uses another
field of a translated entity as a translation and was designed to be used as a fallback
if no other translations are found.
If you wish to extend the bundle, note that it is highly recommended that no classes
except for algorithms depend on drivers.

Three algorithms are shipped with the bundle.

### DefaultAlgorithm

1) The script into the DB looks for the translation into the specified locale.

2) If not found, the script looks for the translation into a locale specified by
```getDefaultLocale()``` method of your locale retriever.

3) If the translation into that default locale is found, and Google Translation
is enabled, the script attempts to get translation into the specified locale from Google.
If Google driver throws exception, translation from step 2 is returned. 

4) If there is no translation neither to specified locale nor to default locale,
the script attempts to retrieve just any other random translation from the DB.

5) Then, if successful, and if Google Translations is enabled, the script will try
to translate that translation into the specified locale via Google. If Google driver
throws exception, translation from step 4 is returned. 

6) Finally, if no translations exist in the DB, the script will look for ```getTranslationFallback()```
method on the entity and return whatever value it returns, except for ```false```.

7) If ```getTranslationFallback()``` does not exist or returns ```false```, the script
gives up and throws ```TranslationException```.

### NoTranslationAlgorithm

This algorithm does not really translate anything, it just searches for any translation
in the DB (returning the one with least PK value if there are many) and throws ```TranslationException```
if there are none. Second and third arguments to ```TranslationManager::translate()``` are not used. 

### OnDemandAlgorithm

This algorithm behaves pretty close to ```DefaultAlgorithm```, but is less persistent 
in trying to find a translation. It does not use third argument to ```TranslationManager::translate()```.

1) The script looks into the DB for the translation into the specified locale.

2) If there is no translation to specified locale,the script attempts to retrieve just 
any other random translation from the DB.

3) Then, if successful, and if Google Translations is enabled, the script will try
to translate that translation into the specified locale via Google. If Google driver
throws exception, translation from step 2 is returned. 

4) If step 2 is unsuccessful, the script gives up and throws ```TranslationException```.

TranslationUpdater::updateTranslations() method
===============================================

This method provides simple interface for creating and updating translations. It has
three mandatory arguments - the translatable entity, the locale, and the array of values.
The basic usage example is as follows:

```
$translationUpdater = $this->get('vkr_translation.translation_updater');
$translationManager->translate($entity, 'fr', [
    'name' => 'French name',
    'description' => 'French description',
]);
```

The third argument must contain as many field-value pairs as there are translatable fields
on the entity. The method searches for a translation to the given locale and updates the
translatable fields, or creates a translation if it does not yet exist.

This method may also throw ```TranslationException``` if no getter and/or setter is found on
the translation entity or if the translation entity cannot be initialized using ```new```
keyword.

Developing
==========

You able to run phpunit tests into a docker container. 

Run this command to build a container:
```
docker build -t vkr-translation-phpunit --build-arg USER_ID=`id --user` --build-arg GROUP_ID=`id --group` --build-arg USER_NAME=`id -u -n` --build-arg GROUP_NAME=`id -g -n` .
```

For a first run:
```
docker run --name vkr-translation-phpunit -v `pwd`:/var/www/ vkr-translation-phpunit
```

For other times:
```
docker start -i vkr-translation-phpunit
```
