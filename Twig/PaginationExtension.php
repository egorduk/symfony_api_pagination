<?php


namespace Btc\PaginationBundle\Twig;


use Btc\PaginationBundle\Pagination\AbstractPagination;
use Symfony\Bundle\TwigBundle\TwigEngine;

class PaginationExtension extends \Twig_Extension
{
    private $sortingTemplate;
    private $filterTemplate;
    private $navigationTemplate;
    private $environment;

    /**
     * {@inheritDoc}
     */
    public function initRuntime(\Twig_Environment $environment)
    {
        $this->environment = $environment;
    }

    /**
     * @param mixed $filterTemplate
     */
    public function setFilterTemplate($filterTemplate)
    {
        $this->filterTemplate = $filterTemplate;
    }

    /**
     * @param mixed $navigationTemplate
     */
    public function setNavigationTemplate($navigationTemplate)
    {
        $this->navigationTemplate = $navigationTemplate;
    }

    /**
     * @param mixed $sortingTemplate
     */
    public function setSortingTemplate($sortingTemplate)
    {
        $this->sortingTemplate = $sortingTemplate;
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'pagination';
    }

    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter('page_sortable', [$this, 'sortable'], ['is_safe' => ['html']]),
            new \Twig_SimpleFilter('page_filter', [$this, 'filter'], ['is_safe' => ['html']]),
            new \Twig_SimpleFilter('page_navigation', [$this, 'navigation'], ['is_safe' => ['html']]),
        ];
    }

    public function sortable(AbstractPagination $pagination, $field, $title)
    {
        return $this->environment->render($this->sortingTemplate, $pagination->sortable($field, $title));
    }

    public function filter(AbstractPagination $pagination, $filterName)
    {
        return $this->environment->render($this->filterTemplate, $pagination->filter($filterName));
    }

    public function navigation(AbstractPagination $pagination)
    {
        return $this->environment->render($this->navigationTemplate, $pagination->navigation());
    }
}
