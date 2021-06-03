<?php
/**
 * 2018-2021 Alma SAS
 *
 * THE MIT LICENSE
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated
 * documentation files (the "Software"), to deal in the Software without restriction, including without limitation
 * the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and
 * to permit persons to whom the Software is furnished to do so, subject to the following conditions:
 * The above copyright notice and this permission notice shall be included in all copies or substantial portions of the
 * Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE
 * WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF
 * CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS
 * IN THE SOFTWARE.
 *
 * @author    Alma SAS <contact@getalma.eu>
 * @copyright 2018-2021 Alma SAS
 * @license   https://opensource.org/licenses/MIT The MIT License
 */

namespace Alma\MonthlyPayments\Gateway\Config\PaymentPlans;

use Alma\API\Entities\FeePlan;

class PaymentPlanConfig implements PaymentPlanConfigInterface
{
    const TRANSIENT_KEY_MIN_ALLOWED_AMOUNT = 'minAllowedAmount';
    const TRANSIENT_KEY_MAX_ALLOWED_AMOUNT = 'maxAllowedAmount';
    const TRANSIENT_KEY_MERCHANT_FEES = 'merchantFees';
    const TRANSIENT_KEY_CUSTOMER_FEES = 'customerFees';

    /**
     * @var array
     */
    private $data;

    /**
     * PaymentPlanConfig constructor.
     * @param array $data
     */
    public function __construct(array $data = [])
    {
        $this->data = $data;
    }

    /**
     * @return string[]
     */
    public static function transientKeys()
    {
        return [
            self::TRANSIENT_KEY_MIN_ALLOWED_AMOUNT,
            self::TRANSIENT_KEY_MAX_ALLOWED_AMOUNT,
            self::TRANSIENT_KEY_MERCHANT_FEES,
            self::TRANSIENT_KEY_CUSTOMER_FEES,
        ];
    }

    /**
     * @inheritDoc
     */
    public static function keyForFeePlan(FeePlan $plan)
    {
        return self::key(
            $plan->kind,
            intval($plan->installments_count),
            intval($plan->deferred_days),
            intval($plan->deferred_months)
        );
    }

    /**
     * @inheritDoc
     */
    public static function defaultConfigForFeePlan(FeePlan $plan)
    {
        return [
            'kind' => $plan->kind,

            'installmentsCount' => $plan->installments_count,

            'deferredDays' => intval($plan->deferred_days),
            'deferredMonths' => intval($plan->deferred_months),

            'enabled' => $plan->installments_count === 3,

            'minAllowedAmount' => $plan->min_purchase_amount,
            'minAmount' => $plan->min_purchase_amount,

            'maxAllowedAmount' => $plan->max_purchase_amount,
            'maxAmount' => $plan->max_purchase_amount,

            'merchantFees' => [
                'variable' => $plan->merchant_fee_variable,
                'fixed' => $plan->merchant_fee_fixed
            ],
            'customerFees' => [
                'variable' => $plan->customer_fee_variable,
                'fixed' => $plan->customer_fee_fixed
            ]
        ];
    }

    /**
     * @param string $planKind
     * @param int $installmentsCount
     * @param int $deferredDays
     * @param int $deferredMonths
     * @return string
     */
    private static function key(
        string $planKind,
        int $installmentsCount,
        int $deferredDays,
        int $deferredMonths
    )
    {
        return implode(':', [$planKind, $installmentsCount, $deferredDays, $deferredMonths]);
    }

    /**
     * @inheritDoc
     */
    public function toArray()
    {
        return $this->data;
    }

    /**
     * @inheritDoc
     */
    public function planKey():
    {
        return self::key($this->kind(), $this->installmentsCount(), $this->deferredDays(), $this->deferredMonths());
    }

    /**
     * @inheritDoc
     */
    public function getPaymentData():
    {
        if (!$this->isAllowed() || !$this->isEnabled()) {
            return [];
        }

        $data = [
            'installments_count' => $this->installmentsCount(),
        ];

        if ($this->isDeferred()) {
            $data['deferred_days'] = $this->deferredDays();
            $data['deferred_months'] = $this->deferredMonths();
        }

        return $data;
    }

    /**
     * @inheritDoc
     */
    public function kind()
    {
        return $this->data['kind'];
    }

    /**
     * @inheritDoc
     */
    public function isAllowed()
    {
        return $this->data['allowed'];
    }

    /**
     * @inheritDoc
     */
    public function isEnabled()
    {
        return $this->data['enabled'];
    }

    /**
     * @inheritDoc
     */
    public function installmentsCount()
    {
        return $this->data['installmentsCount'];
    }

    /**
     * @inheritDoc
     */
    public function isDeferred()
    {
        return $this->deferredDays() > 0 || $this->deferredMonths() > 0;
    }

    /**
     * @return string|null
     */
    public function deferredType()
    {
        if (!$this->isDeferred()) {
            return null;
        }

        return $this->deferredMonths() > 0 ? 'M' : 'D';
    }

    /**
     * @inheritDoc
     */
    public function deferredDays()
    {
        return intval($this->data['deferredDays']);
    }

    /**
     * @inheritDoc
     */
    public function deferredMonths()
    {
        return intval($this->data['deferredMonths']);
    }

    /**
     * @inheritDoc
     */
    public function deferredDurationInDays()
    {
        return $this->deferredMonths() * 30 + $this->deferredDays();
    }

    /**
     * @inheritDoc
     */
    public function deferredDuration()
    {
        return $this->deferredMonths() ?: $this->deferredDays();
    }

    /**
     * @inheritDoc
     */
    public function minimumAmount()
    {
        return $this->data['minAmount'];
    }

    /**
     * @inheritDoc
     */
    public function setMinimumAmount(int $amount)
    {
        $this->data['minAmount'] = $amount;
    }

    /**
     * @inheritDoc
     */
    public function minimumAllowedAmount()
    {
        return $this->data['minAllowedAmount'];
    }

    /**
     * @inheritDoc
     */
    public function maximumAmount()
    {
        return $this->data['maxAmount'];
    }

    /**
     * @inheritDoc
     */
    public function setMaximumAmount(int $amount)
    {
        $this->data['maxAmount'] = $amount;
    }

    /**
     * @inheritDoc
     */
    public function maximumAllowedAmount()
    {
        return $this->data['maxAllowedAmount'];
    }

    /**
     * @inheritDoc
     */
    public function variableMerchantFees()
    {
        return $this->data['merchantFees']['variable'];
    }

    /**
     * @inheritDoc
     */
    public function fixedMerchantFees()
    {
        return $this->data['merchantFees']['fixed'];
    }

    /**
     * @inheritDoc
     */
    public function variableCustomerFees()
    {
        return $this->data['customerFees']['variable'];
    }

    /**
     * @inheritDoc
     */
    public function fixedCustomerFees()
    {
        return $this->data['customerFees']['fixed'];
    }

    /**
     * @return string|null
     */
    public function logoFileName()
    {
        // TODO: there's gotta be a better way
        if (!$this->isDeferred() && in_array($this->installmentsCount(), [2, 3, 4])) {
            return 'p' . $this->installmentsCount() . 'x_logo.svg';
        }

        return null;
    }
}
