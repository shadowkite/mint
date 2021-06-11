<?php

namespace Mint;

use Doctrine\ORM\EntityManager;

class Controller extends \Controller {
    /**
     * @var EntityManager
     */
    protected EntityManager $em;

    /**
     * @var \Mint\Slp
     */
    protected \Mint\Slp $slp;

    public function __construct() {
        $this->slp = new \Mint\Slp();
        if(isset($_SESSION['walletId'])) {
            $this->slp->setWallet($_SESSION['walletId']);
        }

        $this->em = Db::getEM();
    }
}