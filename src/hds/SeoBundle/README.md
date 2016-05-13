hds\SeoBundle
============
*Para usar en los TWIGs
Parametros:
-Nombre del Bloque definido
-Parametros a reemplazar (Debe pasar el parametro con el mismo formato que se le dio entrada en el header)
{{ get_metas('Main', 'es', {'%casa_name%':'Casa de HDS'})|raw }}
{{ get_metas('Main', app.session.get('app_lang_code')|lower )|raw }}


MyCP:
-Crear src/hds
Pegar el SeoBundle
-Añadir app/AppKernel.php
new hds\SeoBundle\SeoBundle()
-Añadir ()Menu app/config/routing.yml
hds-seobundle:
    resource: "@SeoBundle/Resources/config/routing.yml"
    prefix:   /backend/seo
-Añadir src/MyCp/mycpBundle/Resources/views/layout/backend.html.twig
<div class="accordion-inner">
    <a href="{{ path('hdsseo_block_list') }}"><i class="icon-lock"></i> Seo</a>
</div>
-Poner y correr la migracion 
Version20160430152947.php
-app/config/config.yml (SeoBundle)
assetic:
    debug:          "%kernel.debug%"
    use_controller: false
    bundles: [ FrontEndBundle, mycpBundle, SeoBundle ]
- php app/console assetic:dump
