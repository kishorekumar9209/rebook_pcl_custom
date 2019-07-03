<?php
/**
 * Created by PhpStorm.
 * User: vb
 * Date: 24/9/16
 * Time: 2:59 PM
 */

namespace Drupal\redbook_pcl_custom;

use Drupal\Core\Breadcrumb\Breadcrumb;
use Drupal\Core\Breadcrumb\BreadcrumbBuilderInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Link;
use Drupal\node\Entity\Node;
use Drupal\field_collection\Entity\FieldCollectionItem;


/**
 * {@inheritdoc}
 */
class CustomBreadcrumb implements BreadcrumbBuilderInterface
{

    /**
     * @inheritdoc
     */
    public function applies(RouteMatchInterface $route_match)
    {
        return TRUE;

//        $parameters = $route_match->getParameters()->all();
//        if (!empty($parameters['view_id']) && $parameters['view_id'] == 'fresh_trend_page') {
//            return TRUE;
//        }
//        if (isset($parameters['node'])) {
//            return TRUE;
//        }
    }

    /**
     * @inheritdoc
     */
    public function build(RouteMatchInterface $route_match)
    {
        $breadcrumb = new Breadcrumb();
        $parameters = $route_match->getParameters()->all();
//      dsm($parameters);
        if(isset($parameters['node'])) {
          $parameters['node'] = is_string($parameters['node']) ? \Drupal\node\Entity\Node::load($parameters['node']) : $parameters['node'];
        }
        // BreadCrumb for View page that is Fresh Trends and Produce Index page
//        if (!empty($parameters['view_id']) && $parameters['view_id'] == 'fresh_trend_page') {
//            $breadcrumb->addLink(Link::createFromRoute('Home', '<front>'));
//
//        }
        // Breadcrumb for all produce, fruit, vegetable, organic produce page
      if (!empty($parameters['view_id']) && $parameters['view_id'] == 'all_produce') {
        $breadcrumb->addLink(Link::createFromRoute('Home', '<front>'));

      }
      if (!empty($parameters['view_id']) && $parameters['view_id'] == 'fruit_category') {
        $url_object = \Drupal::service('path.validator')->getUrlIfValid('/produce');
        $route_name = $url_object->getRouteName();
        $breadcrumb->addLink(Link::createFromRoute('Home', '<front>'));
        $breadcrumb->addLink(Link::createFromRoute('Produce', $route_name));

      }
      if (!empty($parameters['view_id']) && $parameters['view_id'] == 'vegetable_category') {
        $url_object = \Drupal::service('path.validator')->getUrlIfValid('/produce');
        $route_name = $url_object->getRouteName();
        $breadcrumb->addLink(Link::createFromRoute('Home', '<front>'));
        $breadcrumb->addLink(Link::createFromRoute('Produce', $route_name));

      }
      if (!empty($parameters['view_id']) && $parameters['view_id'] == 'organic_produce_category') {
        $url_object = \Drupal::service('path.validator')->getUrlIfValid('/produce');
        $route_name = $url_object->getRouteName();
        $breadcrumb->addLink(Link::createFromRoute('Home', '<front>'));
        $breadcrumb->addLink(Link::createFromRoute('Produce', $route_name));

      }

//        BreadCrumb for Node Detail page that is for content type "Produce Category", "Commodity" and "Variety" Pages
        if (isset($parameters['node']) && $parameters['node']->getType() == 'produce_category') {
            $url_object = \Drupal::service('path.validator')->getUrlIfValid('/produce');
            $route_name = $url_object->getRouteName();
            $breadcrumb->addLink(Link::createFromRoute('Produce', $route_name));
        }

        if (isset($parameters['node']) && $parameters['node']->getType() == 'commodity') {

            $url_object = \Drupal::service('path.validator')->getUrlIfValid('/produce');
            $route_name = $url_object->getRouteName();
            $breadcrumb->addLink(Link::createFromRoute('Produce', $route_name));
            $produceCategoryFieldId = $parameters['node']->get('field_cm_fc_produce_category')->getValue();

            foreach ($produceCategoryFieldId as $key => $fieldValue) {
                $fc = FieldCollectionItem::load($fieldValue['value']);
                $fields_array = $fc->toArray();
                $tid = $fields_array['field_fc_produce_category'][0]['target_id'];

                $term_name = \Drupal\taxonomy\Entity\Term::load($tid)->get('name')->value;
                $nodeName = (str_replace(' ', '-', strtolower($term_name)));
                $nodeNameTitle = preg_replace('/[^A-Za-z0-9\-]/', '', $nodeName);

                $url_object = \Drupal::service('path.validator')->getUrlIfValid('/category/' . $nodeNameTitle);
                $route_name = $url_object->getRouteName();
                $route_param = $url_object->getRouteParameters();

                $breadcrumb->addLink(Link::createFromRoute($term_name, $route_name, $route_param));

            }

        }

        if (isset($parameters['node']) && $parameters['node']->getType() == 'variety') {

            $url_object = \Drupal::service('path.validator')->getUrlIfValid('/produce');
            $route_name = $url_object->getRouteName();
            $breadcrumb->addLink(Link::createFromRoute('Produce', $route_name));

            $commodityFieldId = $parameters['node']->get('field_vr_fc_commodity')->getValue();

            foreach ($commodityFieldId as $key => $fieldValue) {
                $fc = FieldCollectionItem::load($fieldValue['value']);
                $fields_array = $fc->toArray();
                $tid = $fields_array['field_fc_commodity'][0]['target_id'];

                $Commodity_term_name[] = \Drupal\taxonomy\Entity\Term::load($tid)->get('name')->value;

                $commodityParent = token_taxonomy_term_load_all_parents($tid, '');

            }

            foreach ($commodityParent as $parentkey => $parentValue) {
                $nodeName = (str_replace(' ', '-', strtolower($parentValue)));
                $nodeNameTitle = preg_replace('/[^A-Za-z0-9\-]/', '', $nodeName);
                $url_object = \Drupal::service('path.validator')->getUrlIfValid('/category/' . $nodeNameTitle);
                $route_name = $url_object->getRouteName();
                $route_param = $url_object->getRouteParameters();

                $breadcrumb->addLink(Link::createFromRoute($parentValue, $route_name, $route_param));

            }

            foreach ($Commodity_term_name as $commodityKey => $commodityValue) {
                $nodeName = (str_replace(' ', '-', strtolower($commodityValue)));
                $nodeNameTitle = preg_replace('/[^A-Za-z0-9\-]/', '', $nodeName);
                $url_object = \Drupal::service('path.validator')->getUrlIfValid('/produce/' . $nodeNameTitle);
                $route_name = $url_object->getRouteName();
                $route_param = $url_object->getRouteParameters();

                $breadcrumb->addLink(Link::createFromRoute($commodityValue, $route_name, $route_param));
            }


        }
//        print '<pre>';print_r($breadcrumb);'</pre>';exit;

        $breadcrumb->addCacheContexts(['route']);
        return $breadcrumb;
    }
}