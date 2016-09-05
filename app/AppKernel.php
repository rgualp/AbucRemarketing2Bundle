<?php

use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Config\Loader\LoaderInterface;

class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = array(
            new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new Symfony\Bundle\SecurityBundle\SecurityBundle(),
            new Symfony\Bundle\TwigBundle\TwigBundle(),
            new Symfony\Bundle\MonologBundle\MonologBundle(),
            new Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle(),
            new Symfony\Bundle\AsseticBundle\AsseticBundle(),
            new Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),
            new Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle(),
            new JMS\TranslationBundle\JMSTranslationBundle(),
            new JMS\AopBundle\JMSAopBundle(),
            new JMS\DiExtraBundle\JMSDiExtraBundle($this),
            new JMS\SecurityExtraBundle\JMSSecurityExtraBundle(),
            new MyCp\mycpBundle\mycpBundle(),
            new Ideup\SimplePaginatorBundle\IdeupSimplePaginatorBundle(),
            new MyCp\FrontEndBundle\FrontEndBundle(),
            new Avalanche\Bundle\ImagineBundle\AvalancheImagineBundle(),
            new BeSimple\I18nRoutingBundle\BeSimpleI18nRoutingBundle(),
            //new Lsw\MemcacheBundle\LswMemcacheBundle(),
            /*new Lsw\MemcacheBundle\LswMemcacheBundle(),*/
            //new BeSimple\SoapBundle\BeSimpleSoapBundle()
            new Doctrine\Bundle\MigrationsBundle\DoctrineMigrationsBundle(),
            new Leezy\PheanstalkBundle\LeezyPheanstalkBundle(),
            new Abuc\RemarketingBundle\AbucRemarketingBundle(),
			new hds\SeoBundle\SeoBundle(),
            new MyCp\CasaModuleBundle\MyCpCasaModuleBundle(),
            new MyCp\PartnerBundle\PartnerBundle(),
            new MyCp\LayoutBundle\LayoutBundle(),
            //new JMS\TranslationBundle\JMSTranslationBundle(),
        );

        if (in_array($this->getEnvironment(), array('dev', 'test'))) {
            $bundles[] = new \Symfony\Bundle\DebugBundle\DebugBundle();
            $bundles[] = new Symfony\Bundle\WebProfilerBundle\WebProfilerBundle();
            $bundles[] = new Sensio\Bundle\DistributionBundle\SensioDistributionBundle();
            $bundles[] = new Sensio\Bundle\GeneratorBundle\SensioGeneratorBundle();
        }

        return $bundles;
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(__DIR__.'/config/config_'.$this->getEnvironment().'.yml');
    }
}
