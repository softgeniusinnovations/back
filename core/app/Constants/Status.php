<?php

namespace App\Constants;

class Status {

    const ENABLE  = 1;
    const DISABLE = 0;

    const YES = 1;
    const NO  = 0;



    const LOSE   = 1;
    const REFUND = 1;

    const VERIFIED   = 1;
    const UNVERIFIED = 0;

    const WINNER = 1;
    const LOSER  = 0;

    const PAYMENT_INITIATE = 0;
    const PAYMENT_SUCCESS  = 1;
    const PAYMENT_PENDING  = 2;
    const PAYMENT_REJECT   = 3;

    const TICKET_OPEN   = 0;
    const TICKET_ANSWER = 1;
    const TICKET_REPLY  = 2;
    const TICKET_CLOSE  = 3;

    const PRIORITY_LOW    = 1;
    const PRIORITY_MEDIUM = 2;
    const PRIORITY_HIGH   = 3;

    const USER_ACTIVE = 1;
    const USER_BAN    = 0;

    const KYC_UNVERIFIED = 0;
    const KYC_PENDING    = 2;
    const KYC_VERIFIED   = 1;

    const SINGLE_BET = 1;
    const MULTI_BET  = 2;

    const DECLARED   = 1;
    const UNDECLARED = 0;

    const BET_UNCONFIRMED = 0;
    const BET_WIN         = 1;
    const BET_PENDING     = 2;
    const BET_LOSE        = 3;
    const BET_REFUNDED    = 4;
    const BET_HALF_WIN   = 5;
    const BET_HALF_LOSS   = 6;


    const FRACTION_ODDS = 1;
    const DECIMAL_ODDS  = 2;

    const QUESTION_LOCKED   = 1;
    const QUESTION_UNLOCKED = 0;

    const OPTION_LOCKED   = 1;
    const OPTION_UNLOCKED = 0;
    
    const BET_DEPOSIT = 1;
    const BET_BONUS = 2;
    const BET_TRAMCARD = 3;
    
    const BUTTON_ACTION_WIN = 1;
    const BUTTON_ACTION_LOSS = 2;
    const BUTTON_ACTION_REFUND = 3;
    const BUTTON_ACTION_HALF_WIN = 4;
    const BUTTON_ACTION_HALF_LOSS = 5;
}
