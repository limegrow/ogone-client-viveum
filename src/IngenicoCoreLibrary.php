<?php

namespace IngenicoClient;

use Ogone\DirectLink\PaymentOperation;
use Ogone\DirectLink\Eci;
use Psr\Log\LoggerInterface;
use Symfony\Component\Translation\Translator;
use Symfony\Component\Translation\Loader\PoFileLoader;

class IngenicoCoreLibrary implements IngenicoCoreLibraryInterface
{
    /**
     * Payment Statuses
     */
    const STATUS_PENDING = 'pending';
    const STATUS_AUTHORIZED = 'authorized';
    const STATUS_CAPTURED = 'captured';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_REFUNDED = 'refunded';
    const STATUS_ERROR = 'error';
    const STATUS_UNKNOWN = 'unknown';

    /**
     * Payment Modes
     */
    const PAYMENT_MODE_REDIRECT = 'REDIRECT';
    const PAYMENT_MODE_INLINE = 'INLINE';
    const PAYMENT_MODE_ALIAS = 'ALIAS';

    /**
     * Return States
     */
    const RETURN_STATE_ACCEPT = 'ACCEPT';
    const RETURN_STATE_DECLINE = 'DECLINE';
    const RETURN_STATE_CANCEL = 'CANCEL';
    const RETURN_STATE_EXCEPTION = 'EXCEPTION';
    const RETURN_STATE_BACK = 'BACK';

    /**
     * Platform Controllers type
     */
    const CONTROLLER_TYPE_PAYMENT = 'payment';
    const CONTROLLER_TYPE_SUCCESS = 'success';
    const CONTROLLER_TYPE_ORDER_SUCCESS = 'order_success';
    const CONTROLLER_TYPE_ORDER_CANCELLED = 'order_cancelled';

    /**
     * Account creation link language mapping
     */
    static $accountCreationLangCodes = [
        'en' => 1,
        'fr' => 2,
        'nl' => 3,
        'it' => 4,
        'de' => 5,
        'es' => 6
    ];

    /**
     * Allowed languages
     * @var array
     */
    static $allowedLanguages = [
        'en_US' => 'English', 'cs_CZ' => 'Czech', 'de_DE' => 'German',
        'dk_DK' => 'Danish', 'el_GR' => 'Greek', 'es_ES' => 'Spanish',
        'fr_FR' => 'French', 'it_IT' => 'Italian', 'ja_JP' => 'Japanese',
        'nl_BE' => 'Flemish', 'nl_NL' => 'Dutch', 'no_NO' => 'Norwegian',
        'pl_PL' => 'Polish', 'pt_PT' => 'Portugese', 'ru_RU' => 'Russian',
        'se_SE' => 'Swedish', 'sk_SK' => 'Slovak', 'tr_TR' => 'Turkish',
    ];

    /**
     * Ingenico Error Codes
     * @var array
     */
    static $errorCodes = [
        '0020001001' => "Authorization failed, please retry",
        '0020001002' => "Authorization failed, please retry",
        '0020001003' => "Authorization failed, please retry",
        '0020001004' => "Authorization failed, please retry",
        '0020001005' => "Authorization failed, please retry",
        '0020001006' => "Authorization failed, please retry",
        '0020001007' => "Authorization failed, please retry",
        '0020001008' => "Authorization failed, please retry",
        '0020001009' => "Authorization failed, please retry",
        '0020001010' => "Authorization failed, please retry",
        '0030001999' => "Our payment system is currently under maintenance, please try later",
        '0050001005' => "Expiry date error",
        '0050001007' => "Requested Operation code not allowed",
        '0050001008' => "Invalid delay value",
        '0050001010' => "Input date in invalid format",
        '0050001013' => "Unable to parse socket input stream",
        '0050001014' => "Error in parsing stream content",
        '0050001015' => "Currency error",
        '0050001016' => "Transaction still posted at end of wait",
        '0050001017' => "Sync value not compatible with delay value",
        '0050001019' => "Transaction duplicate of a pre-existing transaction",
        '0050001020' => "Acceptation code empty while required for the transaction",
        '0050001024' => "Maintenance acquirer differs from original transaction acquirer",
        '0050001025' => "Maintenance merchant differs from original transaction merchant",
        '0050001028' => "Maintenance operation not accurate for the original transaction",
        '0050001031' => "Host application unknown for the transaction",
        '0050001032' => "Unable to perform requested operation with requested currency",
        '0050001033' => "Maintenance card number differs from original transaction card number",
        '0050001034' => "Operation code not allowed",
        '0050001035' => "Exception occurred in socket input stream treatment",
        '0050001036' => "Card length does not correspond to an acceptable value for the brand",
        '0050001068' => "A technical problem occurred, please contact helpdesk",
        '0050001069' => "Invalid check for CardID and Brand",
        '0050001070' => "A technical problem occurred, please contact helpdesk",
        '0050001116' => "Unknown origin IP",
        '0050001117' => "No origin IP detected",
        '0050001118' => "Merchant configuration problem, please contact support",
        '10001001' => "Communication failure",
        '10001002' => "Communication failure",
        '10001003' => "Communication failure",
        '10001004' => "Communication failure",
        '10001005' => "Communication failure",
        '20001001' => "We received an unknown status for the transaction. We will contact your acquirer and update the status of the transaction within one working day. Please check the status later.",
        '20001002' => "We received an unknown status for the transaction. We will contact your acquirer and update the status of the transaction within one working day. Please check the status later.",
        '20001003' => "We received an unknown status for the transaction. We will contact your acquirer and update the status of the transaction within one working day. Please check the status later.",
        '20001004' => "We received an unknown status for the transaction. We will contact your acquirer and update the status of the transaction within one working day. Please check the status later.",
        '20001005' => "We received an unknown status for the transaction. We will contact your acquirer and update the status of the transaction within one working day. Please check the status later.",
        '20001006' => "We received an unknown status for the transaction. We will contact your acquirer and update the status of the transaction within one working day. Please check the status later.",
        '20001007' => "We received an unknown status for the transaction. We will contact your acquirer and update the status of the transaction within one working day. Please check the status later.",
        '20001008' => "We received an unknown status for the transaction. We will contact your acquirer and update the status of the transaction within one working day. Please check the status later.",
        '20001009' => "We received an unknown status for the transaction. We will contact your acquirer and update the status of the transaction within one working day. Please check the status later.",
        '20001010' => "We received an unknown status for the transaction. We will contact your acquirer and update the status of the transaction within one working day. Please check the status later.",
        '20001101' => "A technical problem occurred, please contact helpdesk",
        '20001105' => "We received an unknown status for the transaction. We will contact your acquirer and update the status of the transaction within one working day. Please check the status later.",
        '20001111' => "A technical problem occurred, please contact helpdesk",
        '20002001' => "Origin for the response of the bank can not be checked",
        '20002002' => "Beneficiary account number has been modified during processing",
        '20002003' => "Amount has been modified during processing",
        '20002004' => "Currency has been modified during processing",
        '20002005' => "No feedback from the bank server has been detected",
        '30001001' => "Payment refused by the acquirer",
        '30001002' => "Duplicate request",
        '30001010' => "A technical problem occurred, please contact helpdesk",
        '30001011' => "A technical problem occurred, please contact helpdesk",
        '30001012' => "Card black listed - Contact acquirer",
        '30001015' => "Your merchant's acquirer is temporarily unavailable, please try later or choose another payment method.",
        '30001051' => "A technical problem occurred, please contact helpdesk",
        '30001054' => "A technical problem occurred, please contact helpdesk",
        '30001057' => "Your merchant's acquirer is temporarily unavailable, please try later or choose another payment method.",
        '30001058' => "Your merchant's acquirer is temporarily unavailable, please try later or choose another payment method.",
        '30001060' => "Aquirer indicates that a failure occured during payment processing",
        '30001070' => "RATEPAY Invalid Response Type (Failure)",
        '30001071' => "RATEPAY Missing Mandatory status code field (failure)",
        '30001072' => "RATEPAY Missing Mandatory Result code field (failure)",
        '30001073' => "RATEPAY Response parsing Failed",
        '30001090' => "CVC check required by front end and returned invalid by acquirer",
        '30001091' => "ZIP check required by front end and returned invalid by acquirer",
        '30001092' => "Address check required by front end and returned as invalid by acquirer.",
        '30001100' => "Unauthorized buyer's country",
        '30001101' => "IP country <> card country",
        '30001102' => "Number of different countries too high",
        '30001103' => "unauthorized card country",
        '30001104' => "unauthorized ip address country",
        '30001105' => "Anonymous proxy",
        '30001110' => "If the problem persists, please contact Support, or go to paysafecard's card balance page (https://customer.cc.at.paysafecard.com/psccustomer/GetWelcomePanelServlet?language=en) to see when the amount reserved on your card will be available again.",
        '30001120' => "IP address in merchant's black list",
        '30001130' => "BIN in merchant's black list",
        '30001131' => "Wrong BIN for 3xCB",
        '30001140' => "Card in merchant's card blacklist",
        '30001141' => "Email in blacklist",
        '30001142' => "Passenger name in blacklist",
        '30001143' => "Card holder name in blacklist",
        '30001144' => "Passenger name different from owner name",
        '30001145' => "Time to departure too short",
        '30001149' => "Card Configured in Card Supplier Limit for another relation (CSL)",
        '30001150' => "Card not configured in the system for this customer (CSL)",
        '30001151' => "REF1 not allowed for this relationship (Contract number",
        '30001152' => "Card/Supplier Amount limit reached (CSL)",
        '30001153' => "Card not allowed for this supplier (Date out of contract bounds)",
        '30001154' => "You have reached the usage limit allowed",
        '30001155' => "You have reached the usage limit allowed",
        '30001156' => "You have reached the usage limit allowed",
        '30001157' => "Unauthorized IP country for itinerary",
        '30001158' => "email usage limit reached",
        '30001159' => "Unauthorized card country/IP country combination",
        '30001160' => "Postcode in highrisk group",
        '30001161' => "generic blacklist match",
        '30001162' => "Billing Address is a PO Box",
        '30001180' => "maximum scoring reached",
        '30001997' => "Authorization canceled by simulation",
        '30001998' => "A technical problem occurred, please try again.",
        '30001999' => "Your merchant's acquirer is temporarily unavailable, please try later or choose another payment method.",
        '30002001' => "Payment refused by the financial institution",
        '30021001' => "Call acquirer support call number.",
        '30022001' => "Payment must be approved by the acquirer before execution.",
        '30031001' => "Invalid merchant number.",
        '30041001' => "Retain card.",
        '30051001' => "Authorization declined",
        '30071001' => "Retain card - special conditions.",
        '30121001' => "Invalid transaction",
        '30131001' => "Invalid amount",
        '30131002' => "You have reached the total amount allowed",
        '30141001' => "Invalid card number",
        '30151001' => "Unknown acquiring institution.",
        '30171001' => "Payment method cancelled by the buyer",
        '30171002' => "The maximum time allowed is elapsed.",
        '30191001' => "Try again later.",
        '30201001' => "A technical problem occurred, please contact helpdesk",
        '30301001' => "Invalid format",
        '30311001' => "Unknown acquirer ID.",
        '30331001' => "Card expired.",
        '30341001' => "Suspicion of fraud.",
        '30341002' => "Suspicion of fraud (3rdMan)",
        '30341003' => "Suspicion of fraud (Perseuss)",
        '30341004' => "Suspicion of fraud (ETHOCA)",
        '30381001' => "A technical problem occurred, please contact helpdesk",
        '30401001' => "Invalid function.",
        '30411001' => "Lost card.",
        '30431001' => "Stolen card, pick up",
        '30511001' => "Insufficient funds.",
        '30521001' => "No Authorization. Contact the issuer of your card.",
        '30541001' => "Card expired.",
        '30551001' => "Invalid PIN.",
        '30561001' => "Card not in authorizer's database.",
        '30571001' => "Transaction not permitted on card.",
        '30581001' => "Transaction not allowed on this terminal",
        '30591001' => "Suspicion of fraud.",
        '30601001' => "The merchant must contact the acquirer.",
        '30611001' => "Amount exceeds card ceiling.",
        '30621001' => "Restricted card.",
        '30631001' => "Security policy not respected.",
        '30641001' => "Amount changed from ref. trn.",
        '30681001' => "Tardy response.",
        '30751001' => "PIN entered incorrectly too often",
        '30761001' => "Card holder already contesting.",
        '30771001' => "PIN entry required.",
        '30811001' => "Message flow error.",
        '30821001' => "Authorization center unavailable",
        '30831001' => "Authorization center unavailable",
        '30901001' => "Temporary system shutdown.",
        '30911001' => "Acquirer unavailable.",
        '30921001' => "Invalid card type for acquirer.",
        '30941001' => "Duplicate transaction",
        '30961001' => "Processing temporarily not possible",
        '30971001' => "A technical problem occurred, please contact helpdesk",
        '30981001' => "A technical problem occurred, please contact helpdesk",
        '31011001' => "Unknown acceptance code",
        '31021001' => "Invalid currency",
        '31031001' => "Acceptance code missing",
        '31041001' => "Inactive card",
        '31051001' => "Merchant not active",
        '31061001' => "Invalid expiration date",
        '31071001' => "Interrupted host communication",
        '31081001' => "Card refused",
        '31091001' => "Invalid password",
        '31101001' => "Plafond transaction (majoré du bonus) dépassé",
        '31111001' => "Plafond mensuel (majoré du bonus) dépassé",
        '31121001' => "Plafond centre de facturation dépassé",
        '31131001' => "Plafond entreprise dépassé",
        '31141001' => "Code MCC du fournisseur non autorisé pour la carte",
        '31151001' => "Numéro SIRET du fournisseur non autorisé pour la carte",
        '31161001' => "This is not a valid online banking account",
        '32001004' => "A technical problem occurred, please try again.",
        '34011001' => "Bezahlung mit RatePAY nicht möglich.",
        '39991001' => "A technical problem occurred, please contact the helpdesk of your acquirer",
        '40001001' => "A technical problem occurred, please try again.",
        '40001002' => "A technical problem occurred, please try again.",
        '40001003' => "A technical problem occurred, please try again.",
        '40001004' => "A technical problem occurred, please try again.",
        '40001005' => "A technical problem occurred, please try again.",
        '40001006' => "A technical problem occurred, please try again.",
        '40001007' => "A technical problem occurred, please try again.",
        '40001008' => "A technical problem occurred, please try again.",
        '40001009' => "A technical problem occurred, please try again.",
        '40001010' => "A technical problem occurred, please try again.",
        '40001011' => "A technical problem occurred, please contact helpdesk",
        '40001012' => "Your merchant's acquirer is temporarily unavailable, please try later or choose another payment method.",
        '40001013' => "A technical problem occurred, please contact helpdesk",
        '40001016' => "A technical problem occurred, please contact helpdesk",
        '40001018' => "A technical problem occurred, please try again.",
        '40001019' => "Sorry, an error occurred during processing. Please retry the operation (use back button of the browser). If problem persists, contact your merchant's helpdesk.",
        '40001020' => "Sorry, an error occurred during processing. Please retry the operation (use back button of the browser). If problem persists, contact your merchant's helpdesk.",
        '40001050' => "A technical problem occurred, please contact helpdesk",
        '40001133' => "Authentication failed, the signature of your bank access control server is incorrect",
        '40001134' => "Authentication failed, please retry or cancel.",
        '40001135' => "Authentication temporary unavailable, please retry or cancel.",
        '40001136' => "Technical problem with your browser, please retry or cancel",
        '40001137' => "Your bank access control server is temporary unavailable, please retry or cancel",
        '40001998' => "Temporary technical problem. Please retry a little bit later.",
        '50001001' => "Unknown card type",
        '50001002' => "Card number format check failed for given card number.",
        '50001003' => "Merchant data error",
        '50001004' => "Merchant identification missing",
        '50001005' => "Expiry date error",
        '50001006' => "Amount is not a number",
        '50001007' => "A technical problem occurred, please contact helpdesk",
        '50001008' => "A technical problem occurred, please contact helpdesk",
        '50001009' => "A technical problem occurred, please contact helpdesk",
        '50001010' => "A technical problem occurred, please contact helpdesk",
        '50001011' => "Brand not supported for that merchant",
        '50001012' => "A technical problem occurred, please contact helpdesk",
        '50001013' => "A technical problem occurred, please contact helpdesk",
        '50001014' => "A technical problem occurred, please contact helpdesk",
        '50001015' => "Invalid currency code",
        '50001016' => "A technical problem occurred, please contact helpdesk",
        '50001017' => "A technical problem occurred, please contact helpdesk",
        '50001018' => "A technical problem occurred, please contact helpdesk",
        '50001019' => "A technical problem occurred, please contact helpdesk",
        '50001020' => "A technical problem occurred, please contact helpdesk",
        '50001021' => "A technical problem occurred, please contact helpdesk",
        '50001022' => "A technical problem occurred, please contact helpdesk",
        '50001023' => "A technical problem occurred, please contact helpdesk",
        '50001024' => "A technical problem occurred, please contact helpdesk",
        '50001025' => "A technical problem occurred, please contact helpdesk",
        '50001026' => "A technical problem occurred, please contact helpdesk",
        '50001027' => "A technical problem occurred, please contact helpdesk",
        '50001028' => "A technical problem occurred, please contact helpdesk",
        '50001029' => "A technical problem occurred, please contact helpdesk",
        '50001030' => "A technical problem occurred, please contact helpdesk",
        '50001031' => "A technical problem occurred, please contact helpdesk",
        '50001032' => "A technical problem occurred, please contact helpdesk",
        '50001033' => "A technical problem occurred, please contact helpdesk",
        '50001034' => "A technical problem occurred, please contact helpdesk",
        '50001035' => "A technical problem occurred, please contact helpdesk",
        '50001036' => "Card length does not correspond to an acceptable value for the brand",
        '50001037' => "Purchasing card number for a regular merchant",
        '50001038' => "Non Purchasing card for a Purchasing card merchant",
        '50001039' => "Details sent for a non-Purchasing card merchant, please contact helpdesk",
        '50001040' => "Details not sent for a Purchasing card transaction, please contact helpdesk",
        '50001041' => "Payment detail validation failed",
        '50001042' => "Given transactions amounts (tax,discount,shipping,net,etc…) do not compute correctly together",
        '50001043' => "A technical problem occurred, please contact helpdesk",
        '50001044' => "No acquirer configured for this operation",
        '50001045' => "No UID configured for this operation",
        '50001046' => "Operation not allowed for the merchant",
        '50001047' => "A technical problem occurred, please contact helpdesk",
        '50001048' => "A technical problem occurred, please contact helpdesk",
        '50001049' => "A technical problem occurred, please contact helpdesk",
        '50001050' => "A technical problem occurred, please contact helpdesk",
        '50001051' => "A technical problem occurred, please contact helpdesk",
        '50001052' => "A technical problem occurred, please contact helpdesk",
        '50001053' => "A technical problem occurred, please contact helpdesk",
        '50001054' => "Card number incorrect or incompatible",
        '50001055' => "A technical problem occurred, please contact helpdesk",
        '50001056' => "A technical problem occurred, please contact helpdesk",
        '50001057' => "A technical problem occurred, please contact helpdesk",
        '50001058' => "A technical problem occurred, please contact helpdesk",
        '50001059' => "A technical problem occurred, please contact helpdesk",
        '50001060' => "A technical problem occurred, please contact helpdesk",
        '50001061' => "A technical problem occurred, please contact helpdesk",
        '50001062' => "A technical problem occurred, please contact helpdesk",
        '50001063' => "Card Issue Number does not correspond to range or not present",
        '50001064' => "Start Date not valid or not present",
        '50001066' => "Format of CVC code invalid",
        '50001067' => "The merchant is not enrolled for 3D-Secure",
        '50001068' => "The card number or account number (PAN) is invalid",
        '50001069' => "Invalid check for CardID and Brand",
        '50001070' => "The ECI value given is either not supported, or in conflict with other data in the transaction",
        '50001071' => "Incomplete TRN demat",
        '50001072' => "Incomplete PAY demat",
        '50001073' => "No demat APP",
        '50001074' => "Authorisation too old",
        '50001075' => "VERRes was an error message",
        '50001076' => "DCP amount greater than authorisation amount",
        '50001077' => "Details negative amount",
        '50001078' => "Details negative quantity",
        '50001079' => "Could not decode/decompress received PARes (3D-Secure)",
        '50001080' => "Received PARes was an erereor message from ACS (3D-Secure)",
        '50001081' => "Received PARes format was invalid according to the 3DS specifications (3D-Secure)",
        '50001082' => "PAReq/PARes reconciliation failure (3D-Secure)",
        '50001084' => "Maximum amount reached",
        '50001087' => "The transaction type requires authentication, please check with your bank.",
        '50001090' => "CVC missing at input, but CVC check asked",
        '50001091' => "ZIP missing at input, but ZIP check asked",
        '50001092' => "Address missing at input, but Address check asked",
        '50001095' => "Invalid date of birth",
        '50001096' => "Invalid commodity code",
        '50001097' => "The requested currency and brand are incompatible.",
        '50001111' => "Data validation error",
        '50001113' => "This order has already been processed",
        '50001114' => "Error pre-payment check page access",
        '50001115' => "Request not received in secure mode",
        '50001116' => "Unknown IP address origin",
        '50001117' => "NO IP address origin",
        '50001118' => "Pspid not found or not correct",
        '50001119' => "Password incorrect or disabled due to numbers of errors",
        '50001120' => "Invalid currency",
        '50001121' => "Invalid number of decimals for the currency",
        '50001122' => "Currency not accepted by the merchant",
        '50001123' => "Card type not active",
        '50001124' => "Number of lines don't match with number of payments",
        '50001125' => "Format validation error",
        '50001126' => "Overflow in data capture requests for the original order",
        '50001127' => "The original order is not in a correct status",
        '50001128' => "missing authorization code for unauthorized order",
        '50001129' => "Overflow in refunds requests",
        '50001130' => "Error access to original order",
        '50001131' => "Error access to original history item",
        '50001132' => "The Selected Catalog is empty",
        '50001133' => "Duplicate request",
        '50001134' => "Authentication failed, please retry or cancel.",
        '50001135' => "Authentication temporary unavailable, please retry or cancel.",
        '50001136' => "Technical problem with your browser, please retry or cancel",
        '50001137' => "Your bank access control server is temporary unavailable, please retry or cancel",
        '50001150' => "Fraud Detection, Technical error (IP not valid)",
        '50001151' => "Fraud detection  => technical error (IPCTY unknown or error)",
        '50001152' => "Fraud detection  => technical error (CCCTY unknown or error)",
        '50001153' => "Overflow in redo-authorisation requests",
        '50001170' => "Dynamic BIN check failed",
        '50001171' => "Dynamic country check failed",
        '50001172' => "Error in Amadeus signature",
        '50001174' => "Card Holder Name is too long",
        '50001175' => "Name contains invalid characters",
        '50001176' => "Card number is too long",
        '50001177' => "Card number contains non-numeric info",
        '50001178' => "Card Number Empty",
        '50001179' => "CVC too long",
        '50001180' => "CVC contains non-numeric info",
        '50001181' => "Expiration date contains non-numeric info",
        '50001182' => "Invalid expiration month",
        '50001183' => "Expiration date must be in the future",
        '50001184' => "SHA Mismatch",
        '50001205' => "Missing mandatory fields for billing address.",
        '50001206' => "Missing mandatory field date of birth.",
        '50001207' => "Missing required shopping basket details.",
        '50001208' => "Missing social security number",
        '50001209' => "Invalid country code",
        '50001210' => "Missing yearly salary",
        '50001211' => "Missing gender",
        '50001212' => "Missing email",
        '50001213' => "Missing IP address",
        '50001214' => "Missing part payment campaign ID",
        '50001215' => "Missing invoice number",
        '50001216' => "The alias must be different than the card number",
        '60000001' => "account number unknown",
        '60000003' => "not credited dd-mm-yy",
        '60000005' => "name/number do not correspond",
        '60000007' => "account number blocked",
        '60000008' => "specific direct debit block",
        '60000009' => "account number WKA",
        '60000010' => "administrative reason",
        '60000011' => "account number expired",
        '60000012' => "no direct debit authorisation given",
        '60000013' => "debit not approved",
        '60000014' => "double payment",
        '60000018' => "name/address/city not entered",
        '60001001' => "no original direct debit for revocation",
        '60001002' => "payer’s account number format error",
        '60001004' => "payer’s account at different bank",
        '60001005' => "payee’s account at different bank",
        '60001006' => "payee’s account number format error",
        '60001007' => "payer’s account number blocked",
        '60001008' => "payer’s account number expired",
        '60001009' => "payee’s account number expired",
        '60001010' => "direct debit not possible",
        '60001011' => "creditor payment not possible",
        '60001012' => "payer’s account number unknown WKA-number",
        '60001013' => "payee’s account number unknown WKA-number",
        '60001014' => "impermissible WKA transaction",
        '60001015' => "period for revocation expired",
        '60001017' => "reason for revocation not correct",
        '60001018' => "original run number not numeric",
        '60001019' => "payment ID incorrect",
        '60001020' => "amount not numeric",
        '60001021' => "amount zero not permitted",
        '60001022' => "negative amount not permitted",
        '60001023' => "payer and payee giro account number",
        '60001025' => "processing code (verwerkingscode) incorrect",
        '60001028' => "revocation not permitted",
        '60001029' => "guaranteed direct debit on giro account number",
        '60001030' => "NBC transaction type incorrect",
        '60001031' => "description too large",
        '60001032' => "book account number not issued",
        '60001034' => "book account number incorrect",
        '60001035' => "payer’s account number not numeric",
        '60001036' => "payer’s account number not eleven-proof",
        '60001037' => "payer’s account number not issued",
        '60001039' => "payer’s account number of DNB/BGC/BLA",
        '60001040' => "payee’s account number not numeric",
        '60001041' => "payee’s account number not eleven-proof",
        '60001042' => "payee’s account number not issued",
        '60001044' => "payee’s account number unknown",
        '60001050' => "payee’s name missing",
        '60001051' => "indicate payee’s bank account number instead of 3102",
        '60001052' => "no direct debit contract",
        '60001053' => "amount beyond bounds",
        '60001054' => "selective direct debit block",
        '60001055' => "original run number unknown",
        '60001057' => "payer’s name missing",
        '60001058' => "payee’s account number missing",
        '60001059' => "restore not permitted",
        '60001060' => "bank’s reference (navraaggegeven) missing",
        '60001061' => "BEC/GBK number incorrect",
        '60001062' => "BEC/GBK code incorrect",
        '60001087' => "book account number not numeric",
        '60001090' => "cancelled on request",
        '60001091' => "cancellation order executed",
        '60001092' => "cancelled instead of bended",
        '60001093' => "book account number is a shortened account number",
        '60001094' => "instructing party account number not identical with payer",
        '60001095' => "payee unknown GBK acceptor",
        '60001097' => "instructing party account number not identical with payee",
        '60001099' => "clearing not permitted",
        '60001101' => "payer’s account number not spaces",
        '60001102' => "PAN length not numeric",
        '60001103' => "PAN length outside limits",
        '60001104' => "track number not numeric",
        '60001105' => "track number not valid",
        '60001106' => "PAN sequence number not numeric",
        '60001107' => "domestic PAN not numeric",
        '60001108' => "domestic PAN not eleven-proof",
        '60001109' => "domestic PAN not issued",
        '60001110' => "foreign PAN not numeric",
        '60001111' => "card valid date not numeric",
        '60001112' => "book period number (boekperiodenr) not numeric",
        '60001113' => "transaction number not numeric",
        '60001114' => "transaction time not numeric",
        '60001115' => "transaction no valid time",
        '60001116' => "transaction date not numeric",
        '60001117' => "transaction no valid date",
        '60001118' => "STAN not numeric",
        '60001119' => "instructing party’s name missing",
        '60001120' => "foreign amount (bedrag-vv) not numeric",
        '60001122' => "rate (verrekenkoers) not numeric",
        '60001125' => "number of decimals (aantaldecimalen) incorrect",
        '60001126' => "tariff (tarifering) not B/O/S",
        '60001127' => "domestic costs (kostenbinnenland) not numeric",
        '60001128' => "domestic costs (kostenbinnenland) not higher than zero",
        '60001129' => "foreign costs (kostenbuitenland) not numeric",
        '60001130' => "foreign costs (kostenbuitenland) not higher than zero",
        '60001131' => "domestic costs (kostenbinnenland) not zero",
        '60001132' => "foreign costs (kostenbuitenland) not zero",
        '60001134' => "Euro record not fully filled in",
        '60001135' => "Client currency incorrect",
        '60001136' => "Amount NLG not numeric",
        '60001137' => "Amount NLG not higher than zero",
        '60001138' => "Amount NLG not equal to Amount",
        '60001139' => "Amount NLG incorrectly converted",
        '60001140' => "Amount EUR not numeric",
        '60001141' => "Amount EUR not greater than zero",
        '60001142' => "Amount EUR not equal to Amount",
        '60001143' => "Amount EUR incorrectly converted",
        '60001144' => "Client currency not NLG",
        '60001145' => "rate euro-vv (Koerseuro-vv) not numeric",
        '60001146' => "comma rate euro-vv (Kommakoerseuro-vv) incorrect",
        '60001147' => "acceptgiro distributor not valid",
        '60001148' => "Original run number and/or BRN are missing",
        '60001149' => "Amount/Account number/ BRN different",
        '60001150' => "Direct debit already revoked/restored",
        '60001151' => "Direct debit already reversed/revoked/restored",
        '60001153' => "Payer’s account number not known",
    ];

    /**
     * @var ConnectorInterface
     */
    private $extension;

    /**
     * @var Configuration
     */
    private $configuration;

    /** @var LoggerInterface */
    private $logger;

    /**
     * @var Request
     */
    private $request;

    /**
     * @var Translator
     */
    private $translator;

    /**
     * IngenicoCoreLibrary constructor.
     *
     * @param ConnectorInterface $extension
     */
    public function __construct(ConnectorInterface $extension)
    {
        $this->logger = new \Psr\Log\NullLogger();
        $this->extension = $extension;

        // Initialize settings
        $this->configuration = new Configuration($this->extension, $this);
        $this->configuration->load($this->extension->requestSettings($this->extension->requestSettingsMode()));

        $this->request = new Request($_REQUEST);

        // Initialize translations
        $locale = $this->extension->getLocale();
        $this->translator = new Translator($locale);
        $this->translator->addLoader('po', new PoFileLoader());
        $this->translator->setFallbackLocales(['en_US']);
        $this->translator->setLocale($locale);

        // Load translations
        $directory = __DIR__ . '/../translations';
        $files = scandir($directory);
        foreach ($files as $file) {
            $file = $directory . DIRECTORY_SEPARATOR . $file;
            $info = pathinfo($file);
            if ($info['extension'] !== 'po') {
                continue;
            }

            $filename = $info['filename'];
            list($domain, $locale) = explode('.', $filename);
            $this->translator->addResource('po', $directory . DIRECTORY_SEPARATOR . $info['basename'], $locale, $domain);
        }
    }

    /**
     * Gets Logger.
     *
     * @return LoggerInterface|null
     */
    public function getLogger()
    {
        return $this->logger;
    }

    /**
     * Sets Logger.
     *
     * @param LoggerInterface|null $logger
     *
     * @return $this
     */
    public function setLogger(LoggerInterface $logger = null)
    {
        if ($logger) {
            $this->logger = $logger;
        }

        return $this;
    }

    /**
     * Translate string.
     *
     * @param $id
     * @param array $parameters
     * @param string|null $domain
     * @param string|null $locale
     * @return string
     */
    public function __($id, array $parameters = [], $domain = null, $locale = null)
    {
        return $this->translator->trans($id, $parameters, $domain, $locale);
    }

    /**
     * Get All Translations.
     *
     * @param string $locale
     * @param string|null $domain
     * @return array
     */
    public function getAllTranslations($locale, $domain = null)
    {
        if (!$domain) {
            $result = [];
            $catalogue = $this->translator->getCatalogue($locale)->all();
            foreach ($catalogue as $domain => $translations) {
                $result = array_merge($result, $translations);
            }

            return $result;
        }

        return $this->translator->getCatalogue($locale)->all($domain);
    }

    /**
     * Get Error Description.
     *
     * @param $errorCode
     * @return mixed|string
     */
    public static function getErrorDescription($errorCode)
    {
        if (isset(self::$errorCodes[$errorCode])) {
            return self::$errorCodes[$errorCode];
        }

        return 'Unknown';
    }

    /**
     * Get Default Settings.
     *
     * @return array
     */
    public function getDefaultSettings()
    {
        return $this->configuration->getDefault();
    }

    /**
     * Get Configuration instance.
     *
     * @return Configuration
     */
    public function getConfiguration()
    {
        return $this->configuration;
    }

    /**
     * Returns array with cancel, accept,
     * exception and back url.
     * It does require by CoreLibrary.
     *
     * @param int|null $orderId
     * @param string|null $paymentMode
     * @return array
     */
    private function requestReturnUrls($orderId = null, $paymentMode = null)
    {
        if (!$orderId) {
            $orderId = $this->extension->requestOrderId();
        }

        if (!$paymentMode) {
            $paymentMode = $this->configuration->getPaymentpageType();
        }

        return [
            'accept' => $this->extension->buildPlatformUrl(self::CONTROLLER_TYPE_SUCCESS, [
                'order_id' => $orderId,
                'payment_mode' => $paymentMode,
                'return_state' => self::RETURN_STATE_ACCEPT,
            ]),
            'decline' => $this->extension->buildPlatformUrl(self::CONTROLLER_TYPE_SUCCESS, [
                'order_id' => $orderId,
                'payment_mode' => $paymentMode,
                'return_state' => self::RETURN_STATE_DECLINE,
            ]),
            'exception' => $this->extension->buildPlatformUrl(self::CONTROLLER_TYPE_SUCCESS, [
                'order_id' => $orderId,
                'payment_mode' => $paymentMode,
                'return_state' => self::RETURN_STATE_EXCEPTION,
            ]),
            'cancel' => $this->extension->buildPlatformUrl(self::CONTROLLER_TYPE_SUCCESS, [
                'order_id' => $orderId,
                'payment_mode' => $paymentMode,
                'return_state' => self::RETURN_STATE_CANCEL,
            ]),
            'back' => $this->extension->buildPlatformUrl(self::CONTROLLER_TYPE_SUCCESS, [
                'order_id' => $orderId,
                'payment_mode' => $paymentMode,
                'return_state' => self::RETURN_STATE_BACK,
            ]),
        ];
    }

    /**
     * Process Return Urls.
     *
     * Execute when customer made payment. And payment gateway redirect customer back to Merchant shop.
     * We're should check payment status. And update order status.
     *
     * @return void
     * @throws Exception
     */
    public function processReturnUrls()
    {
        $paymentMode = isset($_REQUEST['payment_mode']) ? $_REQUEST['payment_mode'] : null;
        $returnState = isset($_REQUEST['return_state']) ? $_REQUEST['return_state'] : null;

        switch ($returnState) {
            case self::RETURN_STATE_ACCEPT:
                // When "Skip security check (CVV & 3D Secure)" is enabled then we process saved Alias on the plugin (merchant) side.
                // If payment gateway requested 3DSecure then we should use common method (Redirect) to pass 3DSecure validation.
                // Workaround for 3DSecure mode and Inline method
                if ($paymentMode === self::PAYMENT_MODE_INLINE && $this->request->hasComplus()) {
                    $paymentMode = self::PAYMENT_MODE_REDIRECT;
                }

                // Workaround for Inline method and Redirect failback.
                // Some payment methods don't support Inline payment so we are forcing Redirect return handler.
                if ($paymentMode === self::PAYMENT_MODE_INLINE && $this->request->getPayId() !== null) {
                    $paymentMode = self::PAYMENT_MODE_REDIRECT;
                }

                // Handle return
                // We're should check payment status. And update order status.
                if ($paymentMode === self::PAYMENT_MODE_REDIRECT) {
                    $this->processReturnRedirect();
                } else {
                    // Charge using Alias and final order payment validation. Used for Alias payments and Inline (Flex Checkout).
                    $this->processReturnInline();
                }
                break;
            case self::RETURN_STATE_CANCEL:
            case self::RETURN_STATE_BACK:
                // Or customer wants cancel.
                $this->extension->showCancellationTemplate(
                    [
                        'order_id' => $_REQUEST['order_id']
                    ],
                    new Payment(['ORDERID' => $_REQUEST['order_id']])
                );
                break;
            case self::RETURN_STATE_DECLINE:
            case self::RETURN_STATE_EXCEPTION:
                // Error occurred
                $payment = new Payment($_REQUEST);

                // Error for Inline/FlexCheckout
                if (isset($_REQUEST['Alias_NCError'])) {
                    $payment->setNcError($_REQUEST['Alias_NCError']);
                }

                // Error for Inline/FlexCheckout, CardError
                if (isset($_REQUEST['Alias_NCErrorCardNo'])) {
                    $payment->setNcError($_REQUEST['Alias_NCErrorCardNo']);
                }

                // Debug log
                $this->logger->debug(sprintf('%s::%s %s An error occurred. PaymentID: %s. Status: %s. Details: %s %s.',
                    __CLASS__,
                    __METHOD__,
                    __LINE__,
                    $payment->getPayId(),
                    $payment->getStatus(),
                    $payment->getErrorCode(),
                    $payment->getErrorMessage()
                ), [$payment->toArray(), $_GET, $_POST]);

                $this->extension->showPaymentErrorTemplate(
                    [
                        'order_id' => $payment->getOrderId(),
                        'pay_id' => $payment->getPayId(),
                        'message' => $this->__('checkout.error', [
                            '%payment_id%' => (int) $payment->getPayId(),
                            '%status%' => $payment->getStatus(),
                            '%code%' => $payment->getErrorCode(),
                            '%message%' => $payment->getErrorMessage()
                        ], 'messages')
                    ],
                    $payment
                );

                break;
            default:
                throw new Exception('Unknown return state');
        }
    }

    /**
     * Process Redirect payment return request.
     * Check transaction results. Finalize order status.
     * Result: Redirect to Order Success/Cancelled Page.
     *
     * @return void
     * @throws Exception
     */
    private function processReturnRedirect()
    {
        $valid = $this->validatePaymentResponse($_REQUEST);
        if ($_REQUEST['BRAND'] === 'Bancontact/Mister Cash') {
            // @todo Temporary bypass it. Strange, but validation failed for Bancontact.
            $valid = true;
        }

        if (!$valid) {
            throw new Exception('Validation of payment response is failed.');
        }

        // Workaround: Bancontact returns 'Bancontact/Mister Cash' as brand instead of BCMC
        if (isset($_REQUEST['BRAND']) && $_REQUEST['BRAND'] === 'Bancontact/Mister Cash') {
            $_REQUEST['BRAND'] = 'BCMC';
        }

        // Get Payment status
        $orderId = $_REQUEST['orderID'];
        $payId = $_REQUEST['PAYID'];
        $paymentResult = $this->getPaymentInfo($orderId, $payId);

        // Save payment results and update order status
        $this->finaliseOrderPayment($orderId, $paymentResult);

        // Save Alias
        if ($this->configuration->getSettingsOneclick()) {
            $this->processAlias($orderId, [
                'ALIAS' => $paymentResult->getAlias(),
                'BRAND' => $paymentResult->getBrand(),
                'CARDNO' => $paymentResult->getCardNo(),
                'BIN' => $paymentResult->getBin(),
                'PM' => $paymentResult->getPm(),
                'ED' => $paymentResult->getEd(),
            ]);
        }

        // Check is Payment Successful
        if ($paymentResult->isPaymentSuccessful()) {
            // Show "Order success" page
            $this->extension->showSuccessTemplate(
                [
                    'type' => IngenicoCoreLibrary::PAYMENT_MODE_REDIRECT,
                    'order_id' => $orderId,
                    'pay_id' => $payId,
                    'payment_status' => $paymentResult->getPaymentStatus(),
                    'is_show_warning' => $paymentResult->getPaymentStatus() === self::STATUS_AUTHORIZED &&
                        $this->configuration->isTestMode()
                ],
                $paymentResult
            );
        } elseif ($paymentResult->isPaymentCancelled()) {
            // Show "Order cancelled" page
            $this->extension->showCancellationTemplate(
                [
                    'order_id' => $orderId,
                    'pay_id' => $payId,
                ],
                $paymentResult
            );
        } else {
            // Show "Payment error" page
            // Payment error or declined.
            $this->extension->showPaymentErrorTemplate(
                [
                    'order_id' => $orderId,
                    'pay_id' => $payId,
                    'message' => $this->__('checkout.error', [
                        '%payment_id%' => (int) $paymentResult->getPayId(),
                        '%status%' => $paymentResult->getStatus(),
                        '%code%' => $paymentResult->getErrorCode(),
                        '%message%' => $paymentResult->getErrorMessage()
                    ], 'messages')
                ],
                $paymentResult
            );
        }
    }

    /**
     * Process Inline payment return request.
     *
     * @return void
     * @throws Exception
     */
    private function processReturnInline()
    {
        $orderId = $_REQUEST['Alias_OrderId'];
        $aliasId = $_REQUEST['Alias_AliasId'];
        if (empty($orderId) || empty($aliasId)) {
            throw new Exception('Validation error');
        }

        // Get Card Brand
        // Workaround: Bancontact returns 'Bancontact/Mister Cash' as brand instead of BCMC
        if (isset($_REQUEST['Card_Brand'])) {
            if ($_REQUEST['Card_Brand'] === 'Bancontact/Mister Cash') {
                $_REQUEST['Card_Brand'] = 'BCMC';
            }
        } else {
            $_REQUEST['Card_Brand'] = null;
        }

        // Save Alias
        if ($this->configuration->getSettingsOneclick()) {
            $this->processAlias($orderId, [
                'ALIAS' => $_REQUEST['Alias_AliasId'],
                'BRAND' => $_REQUEST['Card_Brand'],
                'CARDNO' => $_REQUEST['Card_CardNumber'],
                'BIN' => $_REQUEST['Card_Bin'],
                'PM' => 'CreditCard',
                'ED' => $_REQUEST['Card_ExpiryDate'],
            ]);
        }

        // Alias saved. But we're should charge payment using Ajax.
        // Show loader
        $this->extension->showInlineLoaderTemplate(
            [
                'type' => IngenicoCoreLibrary::PAYMENT_MODE_INLINE,
                'order_id' => $_REQUEST['Alias_OrderId'],
                'alias_id' => $_REQUEST['Alias_AliasId'],
                'card_brand' => $_REQUEST['Card_Brand'],
                'data' => $_REQUEST
            ]
        );
    }

    /**
     * Executed on the moment when customer's alias saved, and we're should charge payment.
     * Used in Inline payment mode.
     *
     * @param $orderId
     * @param $cardBrand
     * @param $aliasId
     *
     * @return array
     */
    public function finishReturnInline($orderId, $cardBrand, $aliasId)
    {
        // Get PaymentMethod by Card Brand
        $paymentMethod = $this->getPaymentMethodByBrand($cardBrand);

        $alias = new Alias();
        $alias->setAlias($aliasId)
            ->setBrand($cardBrand)
            ->setPm($paymentMethod->getPM())
            ->setForceSecurity(true);

        // Charge payment using Alias
        $paymentResult = $this->executePayment($orderId, $alias);

        // 3DSecure Validation required
        if ($paymentResult->isSecurityCheckRequired()) {
            return [
                'status' => '3ds_required',
                'order_id' => $orderId,
                'pay_id' => $paymentResult->getPayId(),
                'html' => $paymentResult->getSecurityHTML(),
            ];
        }

        if (!$paymentResult->isTransactionSuccessful()) {
            $message = $this->__('checkout.error', [
                '%payment_id%' => $paymentResult->getPayId(),
                '%status%' => $paymentResult->getStatus(),
                '%code%' => $paymentResult->getErrorCode(),
                '%message%' => $paymentResult->getErrorMessage()
            ], 'messages');

            $this->logger->debug(
                sprintf('%s::%s %s Error: An error occurred. PaymentID: %s. Status: %s. Details: %s %s.',
                    __CLASS__,
                    __METHOD__,
                    __LINE__,
                    $paymentResult->getPayId(),
                    $paymentResult->getStatus(),
                    $paymentResult->getErrorCode(),
                    $paymentResult->getErrorMessage()
                ),
                [$paymentResult->toArray(), $_GET, $_POST]
            );

            return [
                'status' => 'error',
                'message' => $message,
                'order_id' => $orderId,
                'pay_id' => $paymentResult->getPayId(),
                'redirect' => $this->extension->buildPlatformUrl(self::CONTROLLER_TYPE_ORDER_CANCELLED, [
                    'order_id' => $orderId
                ]),
            ];
        }

        // Get payment ID
        $payId = $paymentResult->getPayID();

        // Save payment results and update order status
        $this->finaliseOrderPayment($orderId, $paymentResult);

        // Check is Payment Successful
        if ($paymentResult->isPaymentSuccessful()) {
            $this->extension->emptyShoppingCart();

            return [
                'status' => 'success',
                'order_id' => $orderId,
                'pay_id' => $payId,
                'payment_status' => $paymentResult->getPaymentStatus(),
                'redirect' => $this->extension->buildPlatformUrl(self::CONTROLLER_TYPE_ORDER_SUCCESS, [
                    'order_id' => $orderId
                ]),
                'is_show_warning' => $paymentResult->getPaymentStatus() === self::STATUS_AUTHORIZED &&
                    $this->configuration->isTestMode()
            ];
        } elseif ($paymentResult->isPaymentCancelled()) {
            // Cancelled
            $this->extension->restoreShoppingCart();

            return [
                'status' => 'cancelled',
                'order_id' => $orderId,
                'pay_id' => $payId,
                'redirect' => $this->extension->buildPlatformUrl(self::CONTROLLER_TYPE_ORDER_CANCELLED, [
                    'order_id' => $orderId
                ])
            ];
        } else {
            // Payment error or declined.
            $this->extension->restoreShoppingCart();

            return [
                'status' => 'error',
                'order_id' => $orderId,
                'pay_id' => $payId,
                'message' => $this->__('checkout.error', [
                    '%payment_id%' => (int) $paymentResult->getPayId(),
                    '%status%' => $paymentResult->getStatus(),
                    '%code%' => $paymentResult->getErrorCode(),
                    '%message%' => $paymentResult->getErrorMessage()
                ], 'messages'),
                'redirect' => $this->extension->buildPlatformUrl(self::CONTROLLER_TYPE_ORDER_CANCELLED, [
                    'order_id' => $orderId
                ])
            ];
        }
    }

    /**
     * Process Payment Confirmation
     * Execute when customer submit checkout form.
     * We're should initialize payment and display payment form for customer.
     *
     * @param mixed $orderId
     * @param mixed $aliasId
     *
     * @throws Exception
     * @return void
     */
    public function processPayment($orderId, $aliasId = null)
    {
        // Get Payment Mode
        $payment_mode = $this->configuration->getPaymentpageType();

        // Check is Alias Payment mode
        // When "Skip security check (CVV & 3D Secure)" is enabled then process saved Alias on Merchant side.
        if ($this->configuration->getSettingsOneclick() &&
            $this->configuration->getSettingsSkipsecuritycheck() &&
            $aliasId && is_numeric($aliasId))
        {
            $payment_mode = self::PAYMENT_MODE_ALIAS;
        }

        switch ($payment_mode) {
            case self::PAYMENT_MODE_REDIRECT:
                $this->processPaymentRedirect($orderId, $aliasId);
                break;
            case self::PAYMENT_MODE_INLINE:
                $this->processPaymentInline($orderId, $aliasId);
                break;
            case self::PAYMENT_MODE_ALIAS:
                $this->processPaymentAlias($orderId, $aliasId);
                break;
            default:
                throw new Exception('Unknown payment type');
        }
    }

    /**
     * Process Payment Confirmation: Redirect
     *
     * @param mixed $orderId
     * @param mixed $aliasId
     * @throws Exception
     * @return void
     */
    private function processPaymentRedirect($orderId, $aliasId = null)
    {
         if ($this->configuration->getSettingsOneclick()) {
            // Customer chose the saved alias
            $aliasUsage = $this->__('core.authorization_usage');
            if (is_numeric($aliasId)) {
                // Payment with Saved Alias
                $alias = $this->getAlias($aliasId);
                if (!$alias->getId()) {
                    throw new Exception($this->__('exceptions.alias_none'));
                }

                // Check Access
                if ($alias->getCustomerId() != $this->extension->requestCustomerId()) {
                    throw new Exception($this->__('exceptions.access_denied'));
                }

                $alias->setOperation(Alias::OPERATION_BY_PSP)
                    ->setUsage($aliasUsage);
            } else {
                // New alias will be saved
                $alias = new Alias();
                $alias->setIsShouldStoredPermanently(true)
                    ->setOperation(Alias::OPERATION_BY_PSP)
                    ->setUsage($aliasUsage);
            }
        } else {
             $alias = new Alias();
             $alias->setIsPreventStoring(true);
        }

        // Initiate Redirect Payment
        $data = $this->initiateRedirectPayment($orderId, $alias);

        // Show page with list of payment methods
        $this->extension->showPaymentListRedirectTemplate([
            'order_id' => $orderId,
            'url' => $data->getUrl(),
            'fields' => $data->getFields()
        ]);
    }

    /**
     * Process Payment Confirmation: Inline
     *
     * @param mixed $orderId
     * @param mixed $aliasId
     * @return void
     * @throws Exception
     */
    private function processPaymentInline($orderId, $aliasId)
    {
        // One Click Payments
        if ($this->configuration->getSettingsOneclick()) {
            // Customer chose the saved alias
            if (is_numeric($aliasId)) {
                // Payment with the aved alias
                $alias = $this->getAlias($aliasId);
                if (!$alias->getId()) {
                    throw new Exception($this->__('exceptions.alias_none'));
                }

                // Check Access
                if ($alias->getCustomerId() != $this->extension->requestCustomerId()) {
                    throw new Exception($this->__('exceptions.access_denied'));
                }
            } else {
                // New alias will be saved
                $alias = new Alias();
                $alias->setIsShouldStoredPermanently(true);
            }
        } else {
            // Single-use Alias
            $alias = new Alias();
            $alias->setIsShouldStoredPermanently(false);
        }

        // Get Inline Payment Methods
        $inlineMethods = $this->getInlinePaymentMethods($orderId, $alias);

        // Show page with list of payment methods
        $this->extension->showPaymentListInlineTemplate([
            'order_id' => $orderId,
            'categories' => $this->getPaymentCategories(),
            'methods' => $inlineMethods,
            'credit_card_url' => $this->getInlineIFrameUrl($orderId,
                $alias->setPm('CreditCard')->setBrand('')
            )
        ]);
    }

    /**
     * Process Payment Confirmation: Alias
     *
     * @param mixed $orderId
     * @param mixed $aliasId
     * @return void
     * @throws \Exception
     */
    private function processPaymentAlias($orderId, $aliasId = null)
    {
        // Load Alias
        $alias = $this->getAlias($aliasId);
        if (!$alias->getId()) {
            throw new Exception($this->__('exceptions.alias_none'));
        }

        // Check Access
        if ($alias->getCustomerId() != $this->extension->requestCustomerId()) {
            throw new Exception($this->__('exceptions.access_denied'));
        }

        $alias->setOperation(Alias::OPERATION_BY_PSP)
            ->setUsage($this->__('core.authorization_usage'));

        // Charge payment using Alias
        $paymentResult = $this->executePayment($orderId, $alias);

        // 3DSecure Validation required
        if ($paymentResult->isSecurityCheckRequired()) {
            $this->extension->showSecurityCheckTemplate(
                [
                    'html' => $paymentResult->getSecurityHTML()
                ],
                $paymentResult
            );
            return;
        }

        if (!$paymentResult->isTransactionSuccessful()) {
            $message = $this->__('checkout.error', [
                '%payment_id%' => $paymentResult->getPayId(),
                '%status%' => $paymentResult->getStatus(),
                '%code%' => $paymentResult->getErrorCode(),
                '%message%' => $paymentResult->getErrorMessage()
            ], 'messages');

            $this->logger->debug(
                sprintf('%s::%s %s Error: An error occurred. PaymentID: %s. Status: %s. Details: %s %s.',
                    __CLASS__,
                    __METHOD__,
                    __LINE__,
                    $paymentResult->getPayId(),
                    $paymentResult->getStatus(),
                    $paymentResult->getErrorCode(),
                    $paymentResult->getErrorMessage()
                ),
                [$paymentResult->toArray(), $_GET, $_POST]
            );

            throw new Exception($message);
        }

        // Get payment ID
        $payId = $paymentResult->getPayID();

        // Save payment results and update order status
        $this->finaliseOrderPayment($orderId, $paymentResult);

        // Check is Payment Successful
        if ($paymentResult->isPaymentSuccessful()) {
            // Show "Order success" page
            $this->extension->showSuccessTemplate(
                [
                    'type' => IngenicoCoreLibrary::PAYMENT_MODE_INLINE,
                    'order_id' => $orderId,
                    'pay_id' => $payId,
                    'payment_status' => $paymentResult->getPaymentStatus(),
                    'is_show_warning' => $paymentResult->getPaymentStatus() === self::STATUS_AUTHORIZED &&
                        $this->configuration->isTestMode()
                ],
                $paymentResult
            );
        } elseif ($paymentResult->isPaymentCancelled()) {
            // Show "Order cancelled" page
            $this->extension->showCancellationTemplate(
                [
                    'order_id' => $orderId,
                    'pay_id' => $payId,
                ],
                $paymentResult
            );
        } else {
            // Show "Payment error" page
            // Payment error or declined.
            $this->extension->showPaymentErrorTemplate(
                [
                    'order_id' => $orderId,
                    'pay_id' => $payId,
                    'message' => $this->__('checkout.error', [
                        '%payment_id%' => (int) $paymentResult->getPayId(),
                        '%status%' => $paymentResult->getStatus(),
                        '%code%' => $paymentResult->getErrorCode(),
                        '%message%' => $paymentResult->getErrorMessage()
                    ], 'messages')
                ],
                $paymentResult
            );
        }
    }

    /**
     * Get Inline Payment Methods.
     * Returns array with PaymentMethod instances.
     * Every PaymentMethod instance have getIFrameUrl() method.
     * We're use it to render iframes on checkout page.
     *
     * @param $orderId
     * @param Alias $alias
     * @return array
     */
    private function getInlinePaymentMethods($orderId, Alias $alias)
    {
        // Get selected payment methods
        $selectedPaymentMethods = $this->getSelectedPaymentMethods();

        // Get payment method by brand
        if ($alias->getBrand()) {
            try {
                $paymentMethod = $alias->getPaymentMethod();
                if ($paymentMethod) {
                    $selectedPaymentMethods = [
                        $paymentMethod->getId() => $paymentMethod
                    ];
                }
            } catch (\Exception $e) {
                // Silence is golden
            }
        }

        /**
         * @var PaymentMethod\PaymentMethod $paymentMethod
         */
        foreach ($selectedPaymentMethods as $key => $paymentMethod) {
            if (!$paymentMethod->isRedirectOnly()) {
                // Configure Alias's Payment Method and Brand
                $_alias = clone $alias;
                $_alias->setPm($paymentMethod->getPM())
                    ->setBrand($paymentMethod->getBrand());

                $url = $this->getInlineIFrameUrl($orderId, $_alias);
            } else {
                // This Payment Method don't support Inline
                // Use special page for "Redirect" payment
                $url = $this->extension->buildPlatformUrl(self::CONTROLLER_TYPE_PAYMENT, [
                    'pm' => $paymentMethod->getPM(),
                    'brand' => $paymentMethod->getBrand()
                ]);
            }

            // Set iframe Url
            $selectedPaymentMethods[$key]->setIFrameUrl($url);
        }

        return $selectedPaymentMethods;
    }

    /**
     * Create Direct Link payment request.
     *
     * Returns Payment info with transactions results.
     *
     * @param $orderId
     * @param Alias $alias
     *
     * @return Payment
     */
    public function executePayment($orderId, Alias $alias)
    {
        $order = $this->getOrder($orderId);

        // Request Return Urls from Connector
        $urls = $this->requestReturnUrls($orderId);

        if ($this->configuration->getSettingsDirectsales()) {
            $operation = new PaymentOperation(PaymentOperation::REQUEST_FOR_DIRECT_SALE);
        } else {
            $operation = new PaymentOperation(PaymentOperation::REQUEST_FOR_AUTHORISATION);
        }

        $skipSecurityCheck = $this->configuration->getSettingsSkipsecuritycheck();
        $creditDebitFlag = null;

        // Get Assigned PaymentMethod
        try {
            $paymentMethod = $alias->getPaymentMethod();

            // Force 3DSecure
            if ($paymentMethod->isSecurityMandatory()) {
                $skipSecurityCheck = false;
            }

            $creditDebitFlag = $paymentMethod->getCreditDebit();

            // Workaround for Bancontact, Aurore
            // BCMC can't be processed in 2 steps so the OPERATION parameter must contain
            // the value SAL for this payment method. Or don't submit the OPERATION parameter
            // for this payment method and our platform will processes the transaction with the correct operation.
            if ($paymentMethod && !$paymentMethod->isTwoPhaseFlow()) {
                $operation = null;
            }
        } catch (Exception $e) {
            // Silence is golden
        }

        // Force 3DSecure
        if ($alias->getForceSecurity()) {
            $skipSecurityCheck = false;
        }

        return (new DirectLink())
            ->setIsSecure(!$skipSecurityCheck)
            ->setLanguage($this->getLocale($orderId))
            ->setEci(new Eci(Eci::ECOMMERCE_RECURRING))
            ->setCreditDebit($creditDebitFlag)
            ->setLogger($this->getLogger())
            ->createDirectLinkRequest($this->configuration, $order, $alias->exchange(), $operation, $urls);
    }

    /**
     * Get Inline payment method URL
     *
     * @param $orderId
     * @param Alias $alias
     * @return string
     */
    public function getInlineIFrameUrl($orderId, Alias $alias)
    {
        $order = $this->getOrder($orderId);

        // Request Return Urls from Connector
        $urls = $this->requestReturnUrls($orderId, self::PAYMENT_MODE_INLINE);
        $template = $this->configuration->getPaymentpageTemplateName();
        $flexCheckout = new FlexCheckout();
        return $flexCheckout
            ->createFlexCheckout($this->configuration, $order, $alias, $urls, $template)
            ->getCheckoutUrl();
    }

    /**
     * Get payment status.
     *
     * @param $orderId
     * @param $payId
     *
     * @return Payment
     */
    public function getPaymentInfo($orderId, $payId = null)
    {
        $directLink = new DirectLink();
        $directLink->setLogger($this->getLogger());

        $paymentResult = $directLink->createStatusRequest($this->configuration, $orderId, $payId);
        if ($paymentResult) {
            // Set payment status using IngenicoCoreLibarary::getPaymentStatus()
            $paymentResult->setPaymentStatus($this->getPaymentStatus($paymentResult->getBrand(), $paymentResult->getStatus()));
        }

        return $paymentResult;
    }

    /**
     * Get Hosted Checkout HTML.
     *
     * @param $orderId
     * @param Alias $alias
     * @return Data
     */
    public function initiateRedirectPayment($orderId, Alias $alias)
    {
        if ($this->configuration->getSettingsDirectsales()) {
            $operation = new PaymentOperation(PaymentOperation::REQUEST_FOR_DIRECT_SALE);
        } else {
            $operation = new PaymentOperation(PaymentOperation::REQUEST_FOR_AUTHORISATION);
        }

        $order = $this->getOrder($orderId);

        // Redirect method require empty Alias name to generate new Alias
        if ($alias->getIsShouldStoredPermanently()) {
            $alias->setAlias('');
        }

        // Initiate HostedCheckout Payment Request
        $hostedCheckout = (new HostedCheckout())
            ->setConfiguration($this->configuration)
            ->setOrder($order)
            ->setUrls($this->requestReturnUrls($orderId))
            ->setOperation($operation)
            ->setAlias($alias->getIsPreventStoring() ? null : $alias->exchange())
            ->setPm($alias->getPm())
            ->setBrand($alias->getBrand())
            ->setLanguage($order->getLocale())
            ->getPaymentRequest();

        $params = $hostedCheckout->toArray();
        $params['SHASIGN'] = $hostedCheckout->getShaSign();

        if ($this->logger) {
            $this->logger->debug(__CLASS__. '::' . __METHOD__, $params);
        }

        return (new Data())->setUrl($hostedCheckout->getOgoneUri())
            ->setFields($params);
    }

    /**
     * Handle incoming requests by Webhook.
     * Update order's statuses by incoming request from Ingenico.
     * This method should returns http status 200/400.
     *
     * @return void
     */
    public function webhookListener()
    {
        /** @see https://payment-services.ingenico.com/int/en/ogone/support/guides/integration%20guides/e-commerce/transaction-feedback */
        $this->logger->debug('Incoming POST:', $_POST);

        try {
            // Validate
            if (!$this->validatePaymentResponse($_POST)) {
                throw new Exception('WebHook: Validation failed');
            }

            if ($_POST['NCERROR'] !== '0') {
                $details = isset($_POST['NCERRORPLUS']) ? $_POST['NCERRORPLUS'] : '';
                throw new Exception(sprintf('NCERROR: %s. NCERRORPLUS: %s', $_POST['NCERROR'], $details));
            }

            $orderId = $_POST['orderID'];
            $payId = $_POST['PAYID'];

            // Get current order information
            $order = $this->getOrder($orderId);
            if (!$order) {
                throw new Exception(sprintf('WebHook: OrderId %s isn\'t exists.', $orderId));
            }

            // Get Payment Status
            $paymentResult = new Payment($_POST);
            $paymentStatus = $this->getPaymentStatus($paymentResult->getBrand(), $paymentResult->getStatus());

            // Process Order status
            switch ($paymentStatus) {
                case self::STATUS_REFUNDED:
                    try {
                        if (!$this->canRefund($orderId, $payId)) {
                            throw new Exception('Refund unavailable.');
                        }

                        // Save payment results and update order status
                        $this->finaliseOrderPayment($orderId, $paymentResult);
                    } catch (\Exception $e) {
                        // No refund possible
                        $this->logger->debug(sprintf('%s::%s %s %s', __CLASS__, __METHOD__, __LINE__, $e->getMessage()));
                        $this->extension->sendRefundFailedCustomerEmail($orderId);
                        $this->extension->sendRefundFailedAdminEmail($orderId);
                    }

                    break;
                default:
                    try {
                        // Save payment results and update order status
                        $this->finaliseOrderPayment($orderId, $paymentResult);
                    } catch (\Exception $e) {
                        $this->logger->debug(sprintf('%s::%s %s %s', __CLASS__, __METHOD__, __LINE__, $e->getMessage()));
                    }

                    // Process Alias if payment is successful
                    if ($this->configuration->getSettingsOneclick() &&
                        $paymentResult->isPaymentSuccessful()
                    ) {
                        $this->processAlias($orderId, [
                            'ALIAS' => $paymentResult->getAlias(),
                            'BRAND' => $paymentResult->getBrand(),
                            'CARDNO' => $paymentResult->getCardNo(),
                            'BIN' => $paymentResult->getBin(),
                            'PM' => $paymentResult->getPm(),
                            'ED' => $paymentResult->getEd(),
                        ]);
                    }

                    // Notify that order status changed from "cancelled" to "paid" order
                    if (self::STATUS_CANCELLED === $order->getStatus()) {
                        $this->extension->sendOrderPaidCustomerEmail($orderId);
                        $this->extension->sendOrderPaidAdminEmail($orderId);
                    }

                    break;
            }

            http_response_code(200);
            $this->logger->debug(sprintf('WebHook: Success. OrderID: %s. Status: %s', $orderId, $paymentResult->getStatus()));
        } catch (\Exception $e) {
            http_response_code(400);
            $this->logger->debug(sprintf('WebHook: Error: %s', $e->getMessage()));
        }
    }

    /**
     * Get Order.
     *
     * @param $orderId
     *
     * @return Order|false
     */
    private function getOrder($orderId)
    {
        $info = $this->extension->requestOrderInfo($orderId);
        return $info ? new Order($info) : false;
    }

    /**
     * Get Locale.
     *
     * @param $orderId
     *
     * @return string
     */
    private function getLocale($orderId)
    {
        $locale = $this->extension->getLocale($orderId);
        if (!in_array($locale, array_keys(self::$allowedLanguages))) {
            $locale = 'en_US';
        }

        return $locale;
    }

    /**
     * Validate Hosted Checkout return request.
     *
     * @param $request
     *
     * @return mixed
     */
    private function validatePaymentResponse($request)
    {
        $hostedCheckout = new HostedCheckout();
        $hostedCheckout->setLogger($this->getLogger());

        return $hostedCheckout->validate($this->configuration, $request);
    }

    /**
     * Get Country By ISO Code
     *
     * @param $isoCode
     * @return string
     */
    public static function getCountryByCode($isoCode)
    {
        $country = (new \League\ISO3166\ISO3166)->alpha2($isoCode);
        return $country['name'];
    }

    /**
     * Get Categories of Payment Methods
     * @return array
     */
    public function getPaymentCategories()
    {
        $categories = PaymentMethod::getPaymentCategories();

        // Translate categories
        foreach ($categories as $categoryId => $label) {
            $categories[$categoryId] = $this->__($label, [], 'messages');
        }

        return $categories;
    }

    /**
     * Get Countries of Payment Methods.
     * Returns array like ['DE' => 'Germany']
     *
     * @return array
     */
    public function getAllCountries()
    {
        $countries = PaymentMethod::getAllCountries();

        // Translate categories
        foreach ($countries as $code => $label) {
            $countries[$code] = $this->__($label, [], 'messages');
        }


        return $countries;
    }

    /**
     * Get all payment methods.
     *
     * @return array
     */
    public static function getPaymentMethods()
    {
        return PaymentMethod::getPaymentMethods();
    }

    /**
     * @deprecated
     * @return array
     */
    public static function getCountriesPaymentMethods()
    {
        $paymentMethods = new PaymentMethod();

        return $paymentMethods->getCountriesPaymentMethods();
    }

    /**
     * Get Payment Method by Brand.
     *
     * @param $brand
     *
     * @return PaymentMethod\PaymentMethod|false
     */
    public function getPaymentMethodByBrand($brand)
    {
        return PaymentMethod::getPaymentMethodByBrand($brand);
    }

    /**
     * Get payment methods by Category
     *
     * @param $category
     * @return array
     */
    public static function getPaymentMethodsByCategory($category)
    {
        return PaymentMethod::getPaymentMethodsByCategory($category);
    }

    /**
     * Get Selected Payment Methods
     *
     * @return array
     */
    public function getSelectedPaymentMethods()
    {
        $selected = $this->configuration->getSelectedPaymentMethods();
        if (count($selected) === 0) {
            return [];
        }

        // Get All Payment Methods
        $paymentMethods = PaymentMethod::getPaymentMethods();

        // Filter Payment Methods
        /** @var PaymentMethod\PaymentMethod $paymentMethod */
        foreach ($paymentMethods as $key => $paymentMethod) {
            if (!in_array($paymentMethod->getId(), $selected)) {
                unset($paymentMethods[$key]);
            }
        }

        return $paymentMethods;
    }

    /**
     * Get Unused Payment Methods.
     *
     * @return array
     */
    public function getUnusedPaymentMethods()
    {
        $result = [];
        $methods = self::getPaymentMethods();
        $selected = $this->configuration->getSelectedPaymentMethods();

        /** @var PaymentMethod\PaymentMethod $method */
        foreach ($methods as $method) {
            if (!in_array($method->getId(), $selected)) {
                $result[] = $method;
            }
        }

        return $result;
    }

    /**
     * Get Payment Methods by Country ISO code
     * And merge with current list of Payment methods.
     *
     * @param array $countries
     *
     * @return array
     */
    public function getAndMergeCountriesPaymentMethods(array $countries)
    {
        // Get IDs for selected Payment Methods
        $selectedIDs = $this->configuration->getSelectedPaymentMethods();

        // Get Payment methods by Country
        $paymentMethods = self::getPaymentMethods();
        /** @var PaymentMethod\PaymentMethod $method */
        foreach ($paymentMethods as $method) {
            $pmCountries = array_keys($method->getCountries());
            foreach ($countries as $country) {
                if (in_array($country, $pmCountries)) {
                    $selectedIDs[] = $method->getId();
                }
            }
        }

        return array_unique($selectedIDs);
    }

    /**
     * Process Onboarding data and dispatch email to the corresponding Ingenico sales representative.
     *
     * @param string $companyName
     * @param string $email
     * @param string $countryCode
     * @param string $eCommercePlatform
     * @param string $pluginVersion
     * @param $shopName
     * @param $shopLogo
     * @param $shopUrl
     * @param $ingenicoLogo
     * @param string $locale
     *
     * @throws Exception
     */
    public function submitOnboardingRequest(
        $companyName,
        $email,
        $countryCode,
        $eCommercePlatform,
        $pluginVersion,
        $shopName,
        $shopLogo,
        $shopUrl,
        $ingenicoLogo,
        $locale = 'en_US'
    ) {
        $onboarding = new Onboarding();
        if (!$saleEmails = $onboarding->getOnboardingEmailsByCountry($countryCode)) {
            throw new Exception(sprintf('%s country is not found', $countryCode));
        }

        foreach ($saleEmails as $saleEmail) {
            $this->sendMailNotificationOnboardingRequest(
                $saleEmail,
                null,
                null,
                null,
                $this->__('onboarding_request.subject', ['%platform%' => $eCommercePlatform, '%country%' => $countryCode], 'email', $locale),
                [
                    'eCommercePlatform' => $eCommercePlatform,
                    'companyName' => $companyName,
                    'email' => $email,
                    'country' => $countryCode,
                    'requestTimeDate' => new \DateTime('now'),
                    'versionNumber' => $pluginVersion,
                    'shop_name' => $shopName,
                    'shop_logo' => $shopLogo,
                    'shop_url' => $shopUrl,
                    'ingenico_logo' => $ingenicoLogo
                ],
                $locale
            );
        }
    }

    /**
     * Get Payment Status by Status Code.
     *
     * @param $statusCode
     *
     * @return string
     */
    public static function getStatusByCode($statusCode)
    {
        switch ($statusCode) {
            case 1:
            case 6:
            case 61:
            case 62:
                // 1 - Cancelled by customer
                // 6 - Authorised and cancelled
                // 61 - Author. deletion waiting
                // 62 - Author. deletion uncertain
                return self::STATUS_CANCELLED;
            case 5:
            case 50:
            case 51:
            case 52:
            case 59:
                // 5 - Authorised
                // 50 - Authorized waiting external result
                // 51 - Authorisation waiting
                // 52 - Authorisation not known
                // 59 - Authorization to be requested manually
                return self::STATUS_AUTHORIZED;
            case 8:
            case 81:
            case 82:
            case 84:
            case 85:
            case 7:
                // 7 - Payment deleted
                // 8 - Refund
                // 81 - Refund pending
                // 82 - Refund uncertain
                // 84 - Refund
                // 85 - Refund handled by merchant
                return self::STATUS_REFUNDED;
            case 9:
            case 91:
            case 92:
            case 95:
                // 9 - Payment requested
                // 91 - Payment processing
                // 92 - Payment uncertain
                // 95 - Payment handled by merchant (Direct Debit uses this)
                return self::STATUS_CAPTURED;
            case 41:
                // Bank transfer only, both modes: SUCCESS
                return self::STATUS_CAPTURED;
            case 46:
                // 46 - waiting for identification
                return self::STATUS_PENDING;
            default:
                // 0 - Invalid or incomplete
                return self::STATUS_ERROR;
        }
    }

    /**
     * Get Payment Status.
     *
     * @param string $brand
     * @param int $statusCode
     * @return string
     */
    public function getPaymentStatus($brand, $statusCode)
    {
        $paymentMethod = PaymentMethod::getPaymentMethodByBrand($brand);
        if ($paymentMethod) {
            //if (!$this->configuration->getSettingsDirectsales()) {
            //    if (in_array($statusCode, $paymentMethod->getAuthModeSuccessCode())) {
            //        return self::STATUS_AUTHORIZED;
            //    } elseif (in_array($statusCode, $paymentMethod->getDirectSalesSuccessCode())) {
            //        throw new Exception('Impossible to handle SAL transaction when Direct Sales disabled by settings.');
            //    }
            //} else {
            //    if (in_array($statusCode, $paymentMethod->getDirectSalesSuccessCode())) {
            //        return self::STATUS_CAPTURED;
            //    } elseif (in_array($statusCode, $paymentMethod->getAuthModeSuccessCode())) {
            //        throw new Exception('Impossible to handle RES transaction when Direct Sales enabled by settings.');
            //    }
            //}

            if (in_array($statusCode, $paymentMethod->getAuthModeSuccessCode())) {
                return self::STATUS_AUTHORIZED;
            }

            if (in_array($statusCode, $paymentMethod->getDirectSalesSuccessCode())) {
                return self::STATUS_CAPTURED;
            }

            // Payment status is refunded, cancelled?
            $status = self::getStatusByCode($statusCode);
            //if (in_array($status, [self::STATUS_AUTHORIZED, self::STATUS_CAPTURED])) {
                // Success transaction indicates getAuthModeSuccessCode() and getDirectSalesSuccessCode();
                // So we're return error status for safe
                //return self::STATUS_ERROR;
            //}

            return $status;
        }

        return self::getStatusByCode($statusCode);
    }

    /**
     * Finalise Payment and Update order status.
     * Returns payment status as string.
     *
     * @param $orderId
     * @param Payment $paymentResult
     * @return string
     */
    public function finaliseOrderPayment($orderId, Payment &$paymentResult)
    {
        // Log Payment
        $this->extension->logIngenicoPayment($orderId, $paymentResult);

        // Payment result must have status
        if (!$paymentResult->getStatus()) {
            $this->logger->debug(__CLASS__ . '::' . __METHOD__ . ' No status field.', $paymentResult->toArray());
            throw new Exception('An error occurred. Please try to place the order again.');
        }

        // Get Payment Status depend on Brand and Status Number
        $paymentStatus = $this->getPaymentStatus($paymentResult->getBrand(), $paymentResult->getStatus());
        $paymentResult->setPaymentStatus($paymentStatus);

        // Process order
        switch ($paymentStatus) {
            case self::STATUS_AUTHORIZED:
                // Update order status on Connector
                $this->extension->updateOrderStatus(
                    $orderId,
                    $paymentStatus,
                    $this->__('checkout.payment_info', [
                        '%status%' => $paymentStatus,
                        '%status_code%' => $paymentResult->getStatus(),
                        '%payment_id%' => $paymentResult->getPayId(),
                    ], 'messages')
                );

                if ($this->configuration->getDirectSaleEmailOption()) {
                    // Send notifications
                    $this->extension->sendNotificationAuthorization($orderId);
                    $this->extension->sendNotificationAdminAuthorization($orderId);
                }
                break;
            case self::STATUS_CAPTURED:
                // Update order status on Connector
                $this->extension->updateOrderStatus(
                    $orderId,
                    $paymentStatus,
                    $this->__('checkout.payment_info', [
                        '%status%' => $paymentStatus,
                        '%status_code%' => $paymentResult->getStatus(),
                        '%payment_id%' => $paymentResult->getPayId(),
                    ], 'messages')
                );

                $this->extension->addCapturedAmount($orderId, $paymentResult->getAmount());
                break;
            case self::STATUS_REFUNDED:
                // Update order status on Connector
                $this->extension->updateOrderStatus(
                    $orderId,
                    $paymentStatus,
                    $this->__('checkout.payment_info', [
                        '%status%' => $paymentStatus,
                        '%status_code%' => $paymentResult->getStatus(),
                        '%payment_id%' => $paymentResult->getPayId(),
                    ], 'messages')
                );

                $this->extension->addRefundedAmount($orderId, $paymentResult->getAmount());
                break;
            case self::STATUS_CANCELLED:
                // Update order status on Connector
                $this->extension->updateOrderStatus(
                    $orderId,
                    $paymentStatus,
                    $this->__('checkout.payment_info', [
                        '%status%' => $paymentStatus,
                        '%status_code%' => $paymentResult->getStatus(),
                        '%payment_id%' => $paymentResult->getPayId(),
                    ], 'messages')
                );

                $this->extension->addCancelledAmount($orderId, $paymentResult->getAmount());
                break;
            case self::STATUS_ERROR:
                $message = $this->__('checkout.error', [
                    '%payment_id%' => $paymentResult->getPayId(),
                    '%status%' => $paymentResult->getStatus(),
                    '%code%' => $paymentResult->getErrorCode(),
                    '%message%' => $paymentResult->getErrorMessage()
                ], 'messages');

                $paymentResult->setMessage($message);
                $this->logger->debug(__CLASS__ . '::' . __METHOD__ . ' Error: ' . $message, $paymentResult->toArray());

                // Update order status on Connector
                $this->extension->updateOrderStatus($orderId, $paymentStatus, $message);
                break;
            case self::STATUS_UNKNOWN:
                $this->logger->debug(__CLASS__ . '::' . __METHOD__ . ' Unknown status', $paymentResult->toArray());
                break;
        }

        return $paymentStatus;
    }

    /**
     * Check void availability
     *
     * @param $orderId
     * @param $payId
     * @param $cancelAmount
     *
     * @return bool
     */
    public function canVoid($orderId, $payId, $cancelAmount = null)
    {
        $order = $this->getOrder($orderId);

        if (!$cancelAmount) {
            $cancelAmount = $order->getAmount();
        }

        if ($cancelAmount > $order->getAvailableAmountForCancel()) {
            return false;
        }

        $statusCode = $this->getPaymentInfo($orderId, $payId)->getStatus();
        return self::STATUS_AUTHORIZED === $this->getStatusByCode($statusCode);
    }

    /**
     * Check capture availability.
     *
     * @param $orderId
     * @param $payId
     * @param $captureAmount
     *
     * @return bool
     */
    public function canCapture($orderId, $payId, $captureAmount = null)
    {
        $order = $this->getOrder($orderId);

        if (!$captureAmount) {
            $captureAmount = $order->getAmount();
        }

        if ($captureAmount > $order->getAvailableAmountForCapture()) {
            return false;
        }

        $statusCode = $this->getPaymentInfo($orderId, $payId)->getStatus();
        return self::STATUS_AUTHORIZED === $this->getStatusByCode($statusCode);
    }

    /**
     * Check refund availability.
     *
     * @param $orderId
     * @param $payId
     * @param $refundAmount
     *
     * @return bool
     */
    public function canRefund($orderId, $payId, $refundAmount = null)
    {
        $order = $this->getOrder($orderId);

        if (!$refundAmount) {
            $refundAmount = $order->getAmount();
        }

        if ($refundAmount > $order->getAvailableAmountForRefund()) {
            return false;
        }

        $statusCode = $this->getPaymentInfo($orderId, $payId)->getStatus();
        return self::STATUS_CAPTURED === $this->getStatusByCode($statusCode);
    }

    /**
     * Cancel.
     *
     * @param $orderId
     * @param string $payId
     * @param int    $amount
     *
     * @return Payment
     * @throws Exception
     */
    public function cancel($orderId, $payId = null, $amount = null)
    {
        $order = $this->getOrder($orderId);

        $orderAmount = $order->getAmount();
        if (!$amount) {
            $amount = $orderAmount;
        }

        if (!$this->canVoid($orderId, $payId, $amount)) {
            throw new Exception($this->__('exceptions.cancellation_unavailable', [], 'messages'));
        }

        $isPartially = false;
        if ($amount < $order->getAvailableAmountForCancel()) {
            $isPartially = true;
        }

        $directLink = new DirectLink();
        $directLink->setLogger($this->getLogger());

        $response = $directLink->createVoid($this->configuration, $orderId, $payId, $amount, $isPartially);
        if (!$response->isTransactionSuccessful()) {
            throw new Exception(
                $this->__('exceptions.cancellation_failed', [
                    '%code%' => $response->getErrorCode(),
                    '%message%' => $response->getErrorMessage()
                ], 'messages'),
                $response->getErrorCode()
            );
        }

        // Save payment results and update order status
        $this->finaliseOrderPayment($orderId, $response);

        return $response;
    }

    /**
     * Capture.
     *
     * @param $orderId
     * @param string $payId
     * @param int    $amount
     *
     * @return Payment
     * @throws Exception
     */
    public function capture($orderId, $payId = null, $amount = null)
    {
        $order = $this->getOrder($orderId);

        if (!$amount) {
            $amount = $order->getAmount();
        }

        if (!$this->canCapture($orderId, $payId, $amount)) {
            throw new Exception($this->__('exceptions.capture_unavailable', [], 'messages'));
        }

        $isPartially = false;
        if ($amount < $order->getAvailableAmountForCapture()) {
            $isPartially = true;
        }

        $directLink = new DirectLink();
        $directLink->setLogger($this->getLogger());

        $response = $directLink->createCapture($this->configuration, $orderId, $payId, $amount, $isPartially);
        if (!$response->isTransactionSuccessful()) {
            throw new Exception(
                $this->__('exceptions.capture_failed', [
                    '%code%' => $response->getErrorCode(),
                    '%message%' => $response->getErrorMessage()
                ], 'messages'),
                $response->getErrorCode()
            );
        }

        // Save payment results and update order status
        $this->finaliseOrderPayment($orderId, $response);

        return $response;
    }

    /**
     * Refund.
     *
     * @param $orderId
     * @param string $payId
     * @param int    $amount
     *
     * @return Payment
     * @throws Exception
     */
    public function refund($orderId, $payId = null, $amount = null)
    {
        $order = $this->getOrder($orderId);

        if (!$amount) {
            $amount = $order->getAmount();
        }

        if (!$this->canRefund($orderId, $payId, $amount)) {
            throw new Exception($this->__('exceptions.refund_unavailable'));
        }

        $isPartially = false;
        if ($amount < $order->getAvailableAmountForRefund()) {
            $isPartially = true;
        }

        $directLink = new DirectLink();
        $directLink->setLogger($this->getLogger());

        $response = $directLink->createRefund($this->configuration, $orderId, $payId, $amount, $isPartially);
        if (!$response->isTransactionSuccessful()) {
            throw new Exception(
                $this->__('exceptions.refund_failed', [
                    '%code%' => $response->getErrorCode(),
                    '%message%' => $response->getErrorMessage()
                ], 'messages'),
                $response->getErrorCode()
            );
        }

        // Save payment results and update order status
        $this->finaliseOrderPayment($orderId, $response);

        return $response;
    }

    /**
     * Process Alias Save
     * @param $orderId
     * @param array $data
     *
     * @return void
     * @throws Exception
     */
    private function processAlias($orderId, array $data)
    {
        $order = $this->getOrder($orderId);
        $mode = $this->configuration->getPaymentpageType();
        switch ($mode) {
            case self::PAYMENT_MODE_REDIRECT:
                if (!empty($data['ALIAS'])) {
                    // Build Alias instance and save
                    $alias = new Alias($data);
                    $alias->setCustomerId($order->getUserId());
                    $this->saveAlias($alias);
                }
                break;
            case self::PAYMENT_MODE_INLINE:
                if ($this->request->hasAlias() &&
                    $this->request->getAliasStorePermanently() &&
                    $this->request->isAliasStoredSuccess()
                ) {
                    // Build Alias instance and save
                    $alias = new Alias($this->request->getAliasData());
                    $alias->setCustomerId($order->getUserId());
                    $this->saveAlias($alias);
                }
                break;
            default:
                throw new Exception('Unknown payment type.');
        }
    }

    /**
     * @param MailTemplate $template
     * @param string       $to
     * @param string       $toName
     * @param string       $from
     * @param string       $fromName
     * @param string       $subject
     * @param array        $attachedFiles Array like [['name' => 'attached.txt', 'mime' => 'plain/text', 'content' => 'Body']]
     *
     * @return bool
     *
     * @throws Exception
     */
    private function sendMail(
        $template,
        $to,
        $toName,
        $from,
        $fromName,
        $subject,
        array $attachedFiles = []
    ) {
        if (!$template instanceof MailTemplate) {
            throw new Exception('Template variable must be instance of MailTemplate');
        }

        return $this->extension->sendMail(
            $template,
            $to,
            $toName,
            $from,
            $fromName,
            $subject,
            $attachedFiles
        );
    }

    /**
     * Get MailTemplate instance of Reminder.
     *
     * @param $to
     * @param $toName
     * @param $from
     * @param $fromName
     * @param $subject
     * @param array $fields
     * @param string $locale
     *
     * @return bool
     *
     * @throws \Exception
     */
    public function sendMailNotificationReminder(
        $to,
        $toName,
        $from,
        $fromName,
        $subject,
        $fields = array(),
        $locale = null
    ) {
        return $this->sendMail(
            new MailTemplate(
                $locale ?: $this->extension->getLocale(),
                MailTemplate::LAYOUT_DEFAULT,
                MailTemplate::MAIL_TEMPLATE_REMINDER,
                $fields
            ),
            $to,
            $toName,
            $from,
            $fromName,
            $subject
        );
    }

    /**
     * Get MailTemplate instance of "Refund Failed".
     *
     * @param $to
     * @param $toName
     * @param $from
     * @param $fromName
     * @param $subject
     * @param array $fields
     * @param string $locale
     *
     * @return bool
     *
     * @throws \Exception
     */
    public function sendMailNotificationRefundFailed(
        $to,
        $toName,
        $from,
        $fromName,
        $subject,
        $fields = array(),
        $locale = null
    ) {
        return $this->sendMail(
            new MailTemplate(
                $locale ?: $this->extension->getLocale(),
                MailTemplate::LAYOUT_DEFAULT,
                MailTemplate::MAIL_TEMPLATE_REFUND_FAILED,
                $fields
            ),
            $to,
            $toName,
            $from,
            $fromName,
            $subject
        );
    }

    /**
     * Get MailTemplate instance of "Refund Failed".
     *
     * @param $to
     * @param $toName
     * @param $from
     * @param $fromName
     * @param $subject
     * @param array $fields
     * @param string $locale
     *
     * @return bool
     *
     * @throws \Exception
     */
    public function sendMailNotificationAdminRefundFailed(
        $to,
        $toName,
        $from,
        $fromName,
        $subject,
        $fields = array(),
        $locale = null
    ) {
        return $this->sendMail(
            new MailTemplate(
                $locale ?: $this->extension->getLocale(),
                MailTemplate::LAYOUT_INGENICO,
                MailTemplate::MAIL_TEMPLATE_ADMIN_REFUND_FAILED,
                $fields
            ),
            $to,
            $toName,
            $from,
            $fromName,
            $subject
        );
    }

    /**
     * Get MailTemplate instance of "Order Paid".
     *
     * @param $to
     * @param $toName
     * @param $from
     * @param $fromName
     * @param $subject
     * @param array $fields
     * @param string $locale
     *
     * @return bool
     *
     * @throws \Exception
     */
    public function sendMailNotificationPaidOrder(
        $to,
        $toName,
        $from,
        $fromName,
        $subject,
        $fields = array(),
        $locale = null
    ) {
        return $this->sendMail(
            new MailTemplate(
                $locale ?: $this->extension->getLocale(),
                MailTemplate::LAYOUT_DEFAULT,
                MailTemplate::MAIL_TEMPLATE_PAID_ORDER,
                $fields
            ),
            $to,
            $toName,
            $from,
            $fromName,
            $subject
        );
    }

    /**
     * Get MailTemplate instance of "Admin Order Paid".
     *
     * @param $to
     * @param $toName
     * @param $from
     * @param $fromName
     * @param $subject
     * @param array $fields
     * @param string $locale
     *
     * @return bool
     *
     * @throws \Exception
     */
    public function sendMailNotificationAdminPaidOrder(
        $to,
        $toName,
        $from,
        $fromName,
        $subject,
        $fields = array(),
        $locale = null
    ) {
        return $this->sendMail(
            new MailTemplate(
                $locale ?: $this->extension->getLocale(),
                MailTemplate::LAYOUT_INGENICO,
                MailTemplate::MAIL_TEMPLATE_ADMIN_PAID_ORDER,
                $fields
            ),
            $to,
            $toName,
            $from,
            $fromName,
            $subject
        );
    }

    /**
     * Get MailTemplate instance of "Authorization".
     *
     * @param $to
     * @param $toName
     * @param $from
     * @param $fromName
     * @param $subject
     * @param array $fields
     * @param string $locale
     *
     * @return bool
     *
     * @throws \Exception
     */
    public function sendMailNotificationAuthorization(
        $to,
        $toName,
        $from,
        $fromName,
        $subject,
        $fields = array(),
        $locale = null
    ) {
        return $this->sendMail(
            new MailTemplate(
                $locale ?: $this->extension->getLocale(),
                MailTemplate::LAYOUT_DEFAULT,
                MailTemplate::MAIL_TEMPLATE_AUTHORIZATION,
                $fields
            ),
            $to,
            $toName,
            $from,
            $fromName,
            $subject
        );
    }

    /**
     * Get MailTemplate instance of "Admin Authorization".
     *
     * @param $to
     * @param $toName
     * @param $from
     * @param $fromName
     * @param $subject
     * @param array $fields
     * @param string $locale
     *
     * @return bool
     *
     * @throws \Exception
     */
    public function sendMailNotificationAdminAuthorization(
        $to,
        $toName,
        $from,
        $fromName,
        $subject,
        $fields = array(),
        $locale = null
    ) {
        return $this->sendMail(
            new MailTemplate(
                $locale ?: $this->extension->getLocale(),
                MailTemplate::LAYOUT_INGENICO,
                MailTemplate::MAIL_TEMPLATE_ADMIN_AUTHORIZATION,
                $fields
            ),
            $to,
            $toName,
            $from,
            $fromName,
            $subject
        );
    }

    /**
     * Get MailTemplate instance of "Onboarding request".
     *
     * @param $to
     * @param $toName
     * @param $from
     * @param $fromName
     * @param $subject
     * @param array $fields
     * @param string $locale
     *
     * @return bool
     *
     * @throws \Exception
     */
    public function sendMailNotificationOnboardingRequest(
        $to,
        $toName,
        $from,
        $fromName,
        $subject,
        $fields = array(),
        $locale = null
    ) {
        return $this->sendMail(
            new MailTemplate(
                $locale ?: $this->extension->getLocale(),
                MailTemplate::LAYOUT_INGENICO,
                MailTemplate::MAIL_TEMPLATE_ONBOARDING_REQUEST,
                $fields
            ),
            $to,
            $toName,
            $from,
            $fromName,
            $subject
        );
    }

    /**
     * Get MailTemplate instance of "Ingenico Support".
     *
     * @param $to
     * @param $toName
     * @param $from
     * @param $fromName
     * @param $subject
     * @param array $fields
     * @param string $locale
     * @param array $attachedFiles Array like [['name' => 'attached.txt', 'mime' => 'plain/text', 'content' => 'Body']]
     *
     * @return bool
     *
     * @throws \Exception
     */
    public function sendMailSupport(
        $to,
        $toName,
        $from,
        $fromName,
        $subject,
        $fields = array(),
        $locale = null,
        array $attachedFiles = []
    ) {
        return $this->sendMail(
            new MailTemplate(
                $locale ?: $this->extension->getLocale(),
                MailTemplate::LAYOUT_DEFAULT,
                MailTemplate::MAIL_TEMPLATE_SUPPORT,
                $fields
            ),
            $to,
            $toName,
            $from,
            $fromName,
            $subject,
            $attachedFiles
        );
    }

    /**
     *
     */

    /**
     * Get Alias
     * @param $aliasId
     * @return Alias
     */
    public function getAlias($aliasId)
    {
        $data = $this->extension->getAlias($aliasId);
        return new Alias($data);
    }

    /**
     * Get Aliases by CustomerId
     * @param $customerId
     * @return array
     */
    public function getCustomerAliases($customerId)
    {
        $aliases = [];
        $data = $this->extension->getCustomerAliases($customerId);
        foreach ($data as $value) {
            $aliases[] = new Alias($value);
        }

        return $aliases;
    }

    /**
     * Save Alias
     * @param Alias $alias
     * @return bool
     */
    public function saveAlias(Alias $alias)
    {
        // Don't save aliases for some brands
        if (in_array($alias->getBrand(),
            [
                'PostFinance Card',
                'Direct Debits NL',
                'Direct Debits DE',
                'Direct Debit AT',
                'Dankor',
                'UATP',
                'AIRPLUS',
                'Split Payment'
            ]
        )) {
            return true;
        }

        return $this->extension->saveAlias($alias->getCustomerId(), [
            'ALIAS' => $alias->getAlias(),
            'BRAND' => $alias->getBrand(),
            'CARDNO' => $alias->getCardno(),
            'BIN' => $alias->getBin(),
            'PM' => $alias->getPm(),
            'ED' => $alias->getEd()
        ]);
    }

    /**
     * Cron Handler.
     * Send Reminders.
     * Actualise Order's statuses.
     * We're ask payment gateway and get payment status.
     * And update Platform's order status.
     *
     * @return void
     */
    public function cronHandler()
    {
        // Process Reminder notifications
        if ($this->configuration->getSettingsReminderemail()) {
            // Get Settings
            $days = abs($this->configuration->getSettingsReminderemailDays());

            // Send reminders
            foreach ($this->extension->getPendingReminders() as $orderId) {
                $order = $this->getOrder($orderId);
                if (!$order) {
                    continue;
                }

                // Calculate trigger time
                $triggerTime = strtotime($order->getCreatedAt()) + ($days * 24 * 60 * 60);
                if (time() >= $triggerTime) {
                    // Send Reminder
                    $this->extension->sendReminderNotificationEmail($orderId);
                    $this->extension->setReminderSent($orderId);
                }
            }

            // Get Orders for reminding
            $orders = $this->extension->getOrdersForReminding();
            foreach ($orders as $orderId) {
                $order = $this->getOrder($orderId);
                if (!$order) {
                    continue;
                }

                if (self::STATUS_PENDING === $order->getStatus()) {
                    // Get Payment Status from Ingenico
                    $paymentResult = $this->getPaymentInfo($orderId, $order->getPayId());

                    // Check if Payment is unpaid
                    if (!$paymentResult->isTransactionSuccessful() &&
                        (in_array($paymentResult->getErrorCode(), ['50001130', '50001131']) ||
                            $paymentResult->getNcStatus() === 'none'
                        )
                    ) {
                        // Payment Status is failed. Error: 50001130 unknown orderid 691 for merchant
                        // Payment Status is failed. unknown payid/orderID 3046675410/300062 for merchant
                        // Enqueue Reminder
                        $this->extension->enqueueReminder($orderId);
                    }
                }

                // Get cancelled orders in latest 2 days
                if (self::STATUS_CANCELLED === $order->getStatus() &&
                    ((strtotime($order->getCreatedAt()) >= time()) && (strtotime($order->getCreatedAt()) <= strtotime("-{$days} days")))
                ) {
                    if (!$this->extension->isCartPaid($orderId)) {
                        $this->extension->enqueueReminder($orderId);
                    }
                }
            }
        }
    }
}
