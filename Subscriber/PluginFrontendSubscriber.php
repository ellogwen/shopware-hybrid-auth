<?php
namespace Port1HybridAuth\Subscriber;

use Port1HybridAuth\Service\ConfigurationServiceInterface;
use Port1HybridAuth\Service\SingleSignOnServiceInterface;
use Shopware\Models\Customer\Customer;

class PluginFrontendSubscriber extends AbstractSubscriber
{

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            'Enlight_Controller_Action_PreDispatch_Frontend_Register' => 'onFrontendPostDispatchRegister',
            'Enlight_Controller_Action_PreDispatch_Frontend_Account' => 'onFrontendPostDispatchAccount'
        ];
    }

    /**
     * @param \Enlight_Event_EventArgs $args
     * @throws \Exception
     */
    public function onFrontendPostDispatchRegister(\Enlight_Event_EventArgs $args)
    {
        /** @var \Enlight_View_Default $view */
        $view = self::getEverythingFromArgs($args, 'view');

        /** @var ConfigurationServiceInterface $configurationService */
        $configurationService = $this->container->get('port1_hybrid_auth.configuration_service');

        $enabledProviders = $configurationService->getEnabledProviders();

        $view->assign('providers', $enabledProviders);
        $view->addTemplateDir(
            sprintf(
                '%1$s%2$sResources%2$sviews',
                $this->getPath(),
                \DIRECTORY_SEPARATOR
            )
        );
    }

    /**
     * @param \Enlight_Event_EventArgs $args
     * @throws \Exception
     */
    public function onFrontendPostDispatchAccount(\Enlight_Event_EventArgs $args)
    {
        /** @var \Enlight_Controller_Request_Request $request */
        $request = self::getEverythingFromArgs($args, 'request');
        $actionName = strtolower($request->getActionName());

        if ($actionName === 'profile') {
            /** @var \Enlight_Controller_Action $controller */
            $controller = $args->get('subject');
            $view = $controller->View();
            $view->addTemplateDir(
                sprintf(
                    '%1$s%2$sResources%2$sviews',
                    $this->getPath(),
                    \DIRECTORY_SEPARATOR
                )
            );

            $userId = $controller->get('session')->get('sUserId');            
            if (empty($userId)) {
                return;
            }
            
            /** @var Customer $customer */
            $customer = $controller->get('models')->find(Customer::class, $userId);
            if (empty($customer)) {
                return;
            }

            /** @var ConfigurationServiceInterface $configurationService */
            $configurationService = $this->container->get('port1_hybrid_auth.configuration_service');
            $enabledProviders = $configurationService->getEnabledProviders();

            $isSocialRegistered = false;
            foreach ($enabledProviders as $enabledProvider => $label) {
                $attribute = $customer->getAttribute();
                $getProviderIdentity = 'get' . ucfirst(strtolower($enabledProvider)) . 'Identity';
                if (method_exists($attribute, $getProviderIdentity)) {
                    $identity = $attribute->$getProviderIdentity();
                    if ($identity != '') {
                        $isSocialRegistered = true;
                        break;
                    }
                }
            }
            $view->assign('isSocialRegistered', $isSocialRegistered);
        }

        if ($actionName === 'logout') {
            /** @var SingleSignOnServiceInterface $singleSignOnService */
            $singleSignOnService = $this->container->get('port1_hybrid_auth.single_sign_on_service');
            $singleSignOnService->logout();
        }
    }
}
