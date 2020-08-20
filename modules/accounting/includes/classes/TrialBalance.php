<?php


namespace WeDevs\ERP\Accounting\Includes\Classes;


class TrialBalance
{
    private $db;
    public $totalCashAtBank = 0;
    public $totalLoanAtBank = 0;
    public $cashAtBankBreakdowns = [];
    public $loanAtBankBreakdowns = [];
    public $args;
    public $financialYear;

    public function __construct( $args ) {
        global $wpdb;
        $this->db   = $wpdb;
        $this->args = $args;

        $this->getClosestFinYear();
        $this->setBankData();

    }

    public function getPreviousBalance($chart_id){

    //   $endDate = strtotime('-1 day', strtotime('2015-08-10'));

        $sql = "SELECT  SUM( ld.debit - ld.credit ) AS balance,ledger.name,ld.ledger_id,ledger.chart_id  from {$this->db->prefix}erp_acct_ledger_details as ld
                INNER JOIN {$this->db->prefix}erp_acct_ledgers as ledger ON ledger.id = ld.ledger_id
                WHERE ledger.chart_id = %d  AND ld.trn_date >= '%s' AND  ld.trn_date < '%s'
                GROUP BY ld.ledger_id";

        return $this->db->get_results( $this->db->prepare( $sql, $chart_id,  $this->financialYear['start_date'], $this->args['start_date'] ), ARRAY_A );

    }

    /**
     * get opening balance of ledger
     * @param $chart_id
     * @return array|object|null
     */
    public function getOpeningBalances( $chart_id ) {

        $sql = "SELECT ob.ledger_id, SUM(ob.debit - ob.credit) as balance, ledger.name, ob.chart_id  FROM {$this->db->prefix}erp_acct_opening_balances as ob
                INNER JOIN {$this->db->prefix}erp_acct_ledgers as ledger ON ledger.id = ob.ledger_id
                WHERE  ob.financial_year_id = %d AND ob.chart_id = %d GROUP BY ob.ledger_id";

        return $this->db->get_results( $this->db->prepare( $sql, $this->financialYear['id'], $chart_id ), ARRAY_A );
    }

    /**
     * get  balance from ledger_details of ledger
     * @param $chart_id
     * @return array|object|null
     */
    protected function getLedgerDetails( $chart_id ) {

        $sql = "SELECT  SUM( ld.debit - ld.credit ) AS balance,ledger.name,ld.ledger_id,ledger.chart_id  from {$this->db->prefix}erp_acct_ledger_details as ld
                INNER JOIN {$this->db->prefix}erp_acct_ledgers as ledger ON ledger.id = ld.ledger_id
                WHERE ledger.chart_id = %d  AND ld.trn_date BETWEEN '%s' AND '%s'
                GROUP BY ld.ledger_id";

        return $this->db->get_results( $this->db->prepare( $sql, $chart_id, $this->args['start_date'], $this->args['end_date'] ), ARRAY_A );
    }


    /**
     * generate totalCashAtBank, totalLoanAtBank, cashAtBankBreakdowns, loanAtBankBreakdowns
     */
    public function setBankData() {

        $openingBalances = $this->getOpeningBalances( 7 );

        $bankLedgerData = [];

        // format opening balance data by setting ledger_id as index
        foreach ( $openingBalances as $item ) {
            $bankLedgerData[ $item['ledger_id'] ] = $item;
        }

        $ledgerDetails = $this->getLedgerDetails( 7 );

        // format ledger details data by setting ledger_id as index
        //and merge  ledger data according to ledger id with summation
        foreach ( $ledgerDetails as $ledger ) {

            if ( isset( $bankLedgerData[ $ledger['ledger_id'] ] ) ) {
                $bankLedgerData[ $ledger['ledger_id'] ]['balance'] += $ledger['balance'];
            } else {
                $bankLedgerData[ $ledger['ledger_id'] ] = $ledger;
            }
        }

        $previousLedgerBalance = $this->getPreviousBalance( 7 );

        // format ledger details data by setting ledger_id as index
        //and merge  ledger data according to ledger id with summation
        foreach ( $previousLedgerBalance as $ledger ) {

            if ( isset( $bankLedgerData[ $ledger['ledger_id'] ] ) ) {
                $bankLedgerData[ $ledger['ledger_id'] ]['balance'] += $ledger['balance'];
            } else {
                $bankLedgerData[ $ledger['ledger_id'] ] = $ledger;
            }
        }


        foreach ( $bankLedgerData as $ledger ) {


            if ( (float)$ledger['balance'] > 0 ) {

                $this->totalCashAtBank                              += (float)$ledger['balance'];
                $this->cashAtBankBreakdowns[ $ledger['ledger_id'] ] = $ledger;

            }

            if ( (float)$ledger['balance'] < 0 ) {

                $this->totalLoanAtBank                              += (float)$ledger['balance'];
                $this->loanAtBankBreakdowns[ $ledger['ledger_id'] ] = $ledger;

            }
        }

    }

    /**
     * get closest financial year by start date
     */
    function getClosestFinYear() {

        $sql = "SELECT id, name, start_date, end_date FROM {$this->db->prefix}erp_acct_financial_years WHERE start_date <= '%s' ORDER BY start_date DESC LIMIT 1";

        $this->financialYear = $this->db->get_row( $this->db->prepare( $sql, $this->args['start_date'] ), ARRAY_A );
    }

}
