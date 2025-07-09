<?php

use PaynetEasy\PaynetEasyApi\PaymentData\PaymentTransaction;
use PaynetEasy\PaynetEasyApi\PaymentData\Payment;
use PaynetEasy\PaynetEasyApi\PaymentData\Customer;
use PaynetEasy\PaynetEasyApi\PaymentData\RecurrentCard;

require_once __DIR__ . '/common/autoload.php';
require_once __DIR__ . '/common/functions.php';

session_start();

/**
 * Первый этап обработки платежа.
 * Создание нового платежа, выполнение запроса make-rebill.
 */
if (!isset($_GET['stage']))
{
    /**
     * Обратите внимание, что для выполнения этого запроса необходимо сначала
     * получить id кредитной карты, выполнив запрос create-card-ref
     *
     * @see http://wiki.payneteasy.com/index.php/PnE:Recurrent_Transactions#Process_Recurrent_Payment
     * @see http://wiki.payneteasy.com/index.php/PnE:Recurrent_Transactions#Card_Registration
     * @see http://wiki.payneteasy.com/index.php/PnE:Recurrent_Transactions#Process_Initial_Payment
     *
     * Создадим новый платеж.
     *
     * @see http://wiki.payneteasy.com/index.php/PnE:Recurrent_Transactions#Recurrent_Payment_request_parameters
     * @see \PaynetEasy\PaynetEasyApi\Query\MakeRebillQuery::$requestFieldsDefinition
     * @see \PaynetEasy\PaynetEasyApi\PaymentData\PaymentTransaction
     * @see \PaynetEasy\PaynetEasyApi\PaymentData\Payment
     * @see \PaynetEasy\PaynetEasyApi\PaymentData\Customer
     * @see \PaynetEasy\PaynetEasyApi\PaymentData\RecurrentCard
     * @see \PaynetEasy\PaynetEasyApi\PaymentData\QueryConfig
     * @see common/functions.php, $getQueryConfig()
     */
    $paymentTransaction = new PaymentTransaction(array
    (
        'payment'               => new Payment(array
        (
            'client_id'             => 'CLIENT-112244',
            'description'           => 'This is test payment',
            'amount'                =>  0.99,
            'currency'              => 'USD',
            'customer'              =>  new Customer(array
            (
                'ip_address'            => '127.0.0.1'
            )),
            'recurrent_card_from'   => new RecurrentCard(array
            (
                'paynet_id'             => 8058
            ))
        )),
        'query_config'          =>  $getQueryConfig(),
    ));

    /**
     * Выполним запрос make-rebill
     *
     * @see \PaynetEasy\PaynetEasyApi\PaymentProcessor::executeQuery()
     * @see \PaynetEasy\PaynetEasyApi\Query\MakeRebillQuery::updatePaymentOnSuccess()
     */
    $getPaymentProcessor()->executeQuery('make-rebill', $paymentTransaction);
}
/**
 * Второй этап обработки платежа.
 * Ожидание изменения статуса платежа.
 */
elseif ($_GET['stage'] == 'updateStatus')
{
    /**
     * Запросим статус платежа
     */
    $getPaymentProcessor()->executeQuery('status', $loadPaymentTransaction());
}