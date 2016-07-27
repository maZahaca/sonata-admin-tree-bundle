# sonata-admin-tree-bundle
This bundle integrates [jsTree](https://www.jstree.com/) and [Gedmo Nested Set](https://github.com/stof/StofDoctrineExtensionsBundle) directly to [Sonata Admin](https://sonata-project.org/).

A tree builds itself in asynchronous way. So, it's quite good for big trees.

## Installation

### Install requirements

**SonataAdminBundle**  
\- the SonataAdminBundle provides an installation article here:  
http://symfony.com/doc/current/bundles/SonataAdminBundle/index.html

**StofDoctrineExtensionsBundle**  
\- then you need install StofDoctrineExtensionsBundle  
https://symfony.com/doc/master/bundles/StofDoctrineExtensionsBundle/index.html

**Enable Tree Extension**  
\- nested behavior will implement the standard Nested-Set behavior on your Entity  
https://github.com/Atlantic18/DoctrineExtensions/blob/master/doc/tree.md

### Install TreeBundle

Install it via composer 
```bash
composer install redcode/tree-bundle
```

Register bundle in kernel `./app/AppKernel.php`
```php
class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = array(
            ...
            new RedCode\TreeBundle\RedCodeTreeBundle(),
        );
        ...
    }
}
```

Add following lines to the routing file `./app/config/routing.yml`
```yml
redcode_tree:
    resource: "@RedCodeTreeBundle/Resources/config/routing.yml"
    prefix:   /admin
```


For the entity with enabled [Gedmo Nested Set](https://github.com/Atlantic18/DoctrineExtensions/blob/master/doc/tree.md) do following steps:

Extend Admin class from `\RedCode\TreeBundle\Admin\AbstractTreeAdmin`
```php
class SubjectAdmin extends AbstractTreeAdmin
{
...
}
```

Extend AdminController from `\RedCode\TreeBundle\Controller\TreeAdminController`
```php
class SubjectAdminController extends TreeAdminController
{
...
}
```

When registering admin as a service, provider fourth argument - name of the field that will be shown in the tree. 
```yml
app.admin.subject:
    class: AppBundle\Admin\SubjectAdmin
    arguments: [~, AppBundle\Entity\Subject, AppBundle:SubjectAdmin, 'word']
    tags:
        - {name: sonata.admin, manager_type: orm, group: Search, label: Subject}
```

## How it looks like

![redcode/tree-bundle](http://g.recordit.co/QwdbrR3P9R.gif)
