<?php

use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Extension\SandboxExtension;
use Twig\Markup;
use Twig\Sandbox\SecurityError;
use Twig\Sandbox\SecurityNotAllowedTagError;
use Twig\Sandbox\SecurityNotAllowedFilterError;
use Twig\Sandbox\SecurityNotAllowedFunctionError;
use Twig\Source;
use Twig\Template;

/* __string_template__3710066e9dae897ae9daf468ee7320e30440147c84baad78339316c87fe96c46 */
class __TwigTemplate_804734d002b4ceb8ef590146c933351ab05590af6bc5d7cccfe54d3f44b61efe extends \Twig\Template
{
    private $source;
    private $macros = [];

    public function __construct(Environment $env)
    {
        parent::__construct($env);

        $this->source = $this->getSourceContext();

        $this->parent = false;

        $this->blocks = [
        ];
        $this->sandbox = $this->env->getExtension('\Twig\Extension\SandboxExtension');
        $this->checkSecurity();
    }

    protected function doDisplay(array $context, array $blocks = [])
    {
        $macros = $this->macros;
        // line 1
        echo "<div class=\"container\">
<div class=\"banner\">
<p>IT IS A GOOD TIME FOR THE GREAT TASTE OF BURGERS</p>

<h1>BURGERS</h1>

<h3>WEEK</h3>
</div>
</div>";
    }

    public function getTemplateName()
    {
        return "__string_template__3710066e9dae897ae9daf468ee7320e30440147c84baad78339316c87fe96c46";
    }

    public function getDebugInfo()
    {
        return array (  39 => 1,);
    }

    public function getSourceContext()
    {
        return new Source("{# inline_template_start #}<div class=\"container\">
<div class=\"banner\">
<p>IT IS A GOOD TIME FOR THE GREAT TASTE OF BURGERS</p>

<h1>BURGERS</h1>

<h3>WEEK</h3>
</div>
</div>", "__string_template__3710066e9dae897ae9daf468ee7320e30440147c84baad78339316c87fe96c46", "");
    }
    
    public function checkSecurity()
    {
        static $tags = array();
        static $filters = array();
        static $functions = array();

        try {
            $this->sandbox->checkSecurity(
                [],
                [],
                []
            );
        } catch (SecurityError $e) {
            $e->setSourceContext($this->source);

            if ($e instanceof SecurityNotAllowedTagError && isset($tags[$e->getTagName()])) {
                $e->setTemplateLine($tags[$e->getTagName()]);
            } elseif ($e instanceof SecurityNotAllowedFilterError && isset($filters[$e->getFilterName()])) {
                $e->setTemplateLine($filters[$e->getFilterName()]);
            } elseif ($e instanceof SecurityNotAllowedFunctionError && isset($functions[$e->getFunctionName()])) {
                $e->setTemplateLine($functions[$e->getFunctionName()]);
            }

            throw $e;
        }

    }
}
