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

/* __string_template__5822c88209bf07ddb299f0a1c124826ba8c7a6fc78674eb4493e9314e2f59c7a */
class __TwigTemplate_81da5176358d5834c4c4738c56595875d31fac55244f85f0864b276f1bd1d5ec extends \Twig\Template
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
        echo "<div class=\"menu-slider-item-image\">
";
        // line 2
        echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed(($context["field_add_product_image"] ?? null), 2, $this->source), "html", null, true);
        echo "
</div>
<div class=menu-slider-item-desc\">
<h4 class=\"menu-slider-item-title\"> ";
        // line 5
        echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed(($context["title"] ?? null), 5, $this->source), "html", null, true);
        echo " </h4>
<p class=\"menu-slider-item-subtitle\"> ";
        // line 6
        echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed(($context["body"] ?? null), 6, $this->source), "html", null, true);
        echo " </p>
";
        // line 7
        echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed(($context["product_id"] ?? null), 7, $this->source), "html", null, true);
        echo "
</div>";
    }

    public function getTemplateName()
    {
        return "__string_template__5822c88209bf07ddb299f0a1c124826ba8c7a6fc78674eb4493e9314e2f59c7a";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  56 => 7,  52 => 6,  48 => 5,  42 => 2,  39 => 1,);
    }

    public function getSourceContext()
    {
        return new Source("{# inline_template_start #}<div class=\"menu-slider-item-image\">
{{ field_add_product_image }}
</div>
<div class=menu-slider-item-desc\">
<h4 class=\"menu-slider-item-title\"> {{ title }} </h4>
<p class=\"menu-slider-item-subtitle\"> {{ body }} </p>
{{ product_id }}
</div>", "__string_template__5822c88209bf07ddb299f0a1c124826ba8c7a6fc78674eb4493e9314e2f59c7a", "");
    }
    
    public function checkSecurity()
    {
        static $tags = array();
        static $filters = array("escape" => 2);
        static $functions = array();

        try {
            $this->sandbox->checkSecurity(
                [],
                ['escape'],
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
