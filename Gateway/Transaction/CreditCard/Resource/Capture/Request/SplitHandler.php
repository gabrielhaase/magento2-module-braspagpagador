<?php

namespace Webjump\BraspagPagador\Gateway\Transaction\CreditCard\Resource\Capture\Request;

use Magento\Payment\Gateway\Response\HandlerInterface;
use Webjump\BraspagPagador\Gateway\Transaction\CreditCard\Resource\Capture\Request;
use Webjump\BraspagPagador\Gateway\Transaction\Base\Resource\Request\AbstractHandler;
use Webjump\BraspagPagador\Model\SplitManager;

/**

 * Braspag Transaction CreditCard Capture Response Handler
 *
 * @author      Webjump Core Team <dev@webjump.com>
 * @copyright   2016 Webjump (http://www.webjump.com.br)
 * @license     http://www.webjump.com.br  Copyright
 *
 * @link        http://www.webjump.com.br
 */
class SplitHandler extends AbstractHandler implements HandlerInterface
{
    /**
     * @var
     */
    protected $session;

    /**
     * @var
     */
    protected $splitManager;

    public function __construct(
        SplitManager $splitManager,
        Request $request,
        \Magento\Checkout\Model\Session $session
    ) {
        $this->setSplitManager($splitManager);
        $this->setRequest($request);
        $this->setSession($session);
    }

    /**
     * @return Webjump\BraspagPagador\Model\SplitManager
     */
    public function getSplitManager(): SplitManager
    {
        return $this->splitManager;
    }

    /**
     * @param Webjump\BraspagPagador\Model\SplitManager $splitManager
     */
    public function setSplitManager(SplitManager $splitManager)
    {
        $this->splitManager = $splitManager;
    }

    /**
     * @return mixed
     */
    public function getSession()
    {
        return $this->session;
    }

    /**
     * @param mixed $session
     */
    public function setSession($session)
    {
        $this->session = $session;
    }

    /**
     * @param $payment
     * @param $request
     * @return $this
     */
    protected function _handle($payment, $request)
    {
        if (!$request->getPaymentSplitRequest()) {
            return $this;
        }

        $request->getPaymentSplitRequest()->prepareSplits();

        $splitData = $request->getPaymentSplitRequest()->getSplits();

        $this->getSplitManager()->createPaymentSplitByOrder($payment->getOrder(), $splitData);

        $captureAmount = $this->getCaptureAmount($splitData)/100;

        $request->setCaptureAmount($captureAmount);

        return $request;
    }

    /**
     * @param $splitData
     * @return int
     */
    protected function getCaptureAmount($splitData)
    {
        $captureAmount = 0;
        foreach ($splitData->getSubordinates() as $splitSubordinate) {
            $captureAmount += $splitSubordinate->getAmount();
        }

        return $captureAmount;
    }
}
