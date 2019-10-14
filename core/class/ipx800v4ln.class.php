<?php

/* This file is part of Jeedom.
 *
 * Jeedom is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Jeedom is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
 */

/* * ***************************Includes********************************* */
require_once __DIR__  . '/../../../../core/php/core.inc.php';

class ipx800v4ln extends eqLogic {
    /*     * *************************Attributs****************************** */

    /*     * ***********************Methode static*************************** */
    //gestion des dependances
    public static function dependancy_info() {
     }

    //install des dependances
    public function dependancy_install() {
    }

    // creation de staches cron suivant config de l'equipement
    public static function cron() {
  		$dateRun = new DateTime();
      // log::add('ipx800v4ln', 'debug', "on passe par le cron");
  		foreach (eqLogic::byType('ipx800v4ln') as $eqLogic) {
  			$autorefresh = $eqLogic->getConfiguration('autorefresh');
  			if ($eqLogic->getIsEnable() == 1 && $autorefresh != '') {
  				try {
  					$c = new Cron\CronExpression($autorefresh, new Cron\FieldFactory);
  					if ($c->isDue($dateRun)) {
              $cmd = $eqLogic->getCmd(null, 'refresh');//retourne la commande "refresh si elle existe
    				  if (!is_object($cmd)) {//Si la commande n'existe pas
    				  	continue; //continue la boucle
    				  }
    				  $cmd->execCmd(); // la commande existe on la lance
  					}
  				} catch (Exception $exc) {
  					log::add('ipx800v4ln', 'error', __('Expression cron non valide pour ', __FILE__) . $eqLogic->getHumanName() . ' : ' . $autorefresh);
  				}
  			}
  		}
  	}


    /*     * *********************Méthodes d'instance************************* */

    //fonction de recuperation des infos de l'ipx800v4ln
    public function recupIpx800V4($type) {
      ini_set("allow_url_fopen", 1);
      $ip = $this->getConfiguration("ip");
      $clef = $this->getConfiguration("apikey");
      $url = 'http://'.$ip.'/api/xdevices.json?key='.$clef.'&Get='.$type;
      $obj = json_decode(file_get_contents($url), true);
      log::add('ipx800v4ln', 'debug',"recupnumerique: ".$url." ip: -".$ip."- et resultat: -".$obj['status']."-");
      if ($obj['status']=='Error') {
        			throw new Exception(("Erreur, acces interdit. avez vous rempli la clef d'api?"));
      }
      log::add('ipx800v4ln', 'debug',"recupnumerique: ".$url." ip: -".$ip."- et resultat: -".$obj."-");
      return $obj;
    }

    //fonction de recuperation des infos des relais de l'ipx800v4ln
    public function recupRelais() {
      $tab=array();
      $tab[]='';
      $tmp=$this->recupIpx800V4('R');
      for ($i=1; $i<9; $i++) {
        $tab[]=$tmp['R'.$i];
      }
      log::add('ipx800v4ln', 'debug',"recup relais resultat: -".count($tab)."-");
      return $tab;
    }

    //fonction de recuperation des infos des entree de l'ipx800v4ln
    public function recupEntrees() {
      $tab=array();
      $tab[]='';
      $tmp=$this->recupIpx800V4('D');
      for ($i=1; $i<9; $i++) {
        $tab[]=$tmp['D'.$i];
      }
      log::add('ipx800v4ln', 'debug',"recup entrees resultat: -".count($tab)."-");
      return $tab;
    }

    //fonction de recuperation des infos des entree de l'ipx800v4ln
    public function recupEntreesAnalogiques() {
      $tab=array();
      $tab[]='';
      $tmp=$this->recupIpx800V4('A');
      for ($i=1; $i<5; $i++) {
        $tab[]=$tmp['A'.$i];
      }
      log::add('ipx800v4ln', 'debug',"recup entrees resultat: -".count($tab)."-");
      return $tab;
    }

    //fonction de vrification de la presence de l'equipement sur le reseau
    public function ping() {
      $ip = $this->getConfiguration("ip");
      $ping2 = exec("ping -c 1 ".$ip, $ping, $return);
      log::add('ipx800v4ln', 'debug',"ping de l'equipement: -".$return."-".$ping."-");
      if($return=='1')
      {
         return 0;
      }
      else
      {
         return 1;
      }
    }

    public function preInsert() {
      log::add('ipx800v4ln', 'debug', "preinsert");

    }

    public function postInsert() {
      log::add('ipx800v4ln', 'debug', "postinsert");

    }
    // renseigne l'autorefresh si vide
    public function preSave() {
      log::add('ipx800v4ln', 'debug', "presave");
      if ($this->getConfiguration('autorefresh') == '') {
			     $this->setConfiguration('autorefresh', '*/30 * * * *');
		  }
    }

    public function postSave() {
      log::add('ipx800v4ln', 'debug', "postsave");
      // creation commande refresh
      $refresh = $this->getCmd(null, 'refresh');
  		if (!is_object($refresh)) {
  			$refresh = new ipx800v4lnCmd();
  			$refresh->setName(__('Rafraichir', __FILE__));
  		}
  		$refresh->setEqLogic_id($this->getId());
  		$refresh->setLogicalId('refresh');
  		$refresh->setType('action');
  		$refresh->setSubType('other');
      $refresh->setOrder(1);
      $refresh->setIsHistorized(0);
  		$refresh->save();


      // creation info relais
      if ($this->getConfiguration('relais') == 1) {
          for ($i=1; $i<9; $i++) {
              $relais = $this->getCmd(null, 'relais'.$i);
          		if (!is_object($relais)) {
          			$relais = new ipx800v4lnCmd();
          			$relais->setName(__('Relais '.$i, __FILE__));
          		}
          		$relais->setEqLogic_id($this->getId());
          		$relais->setLogicalId('relais'.$i);
          		$relais->setType('info');
              $relais->setOrder(100);
          		$relais->setSubType('binary');
              $relais->setIsHistorized(1);
          		$relais->save();
          }
      }
      else {
        for ($i=1; $i<=9; $i++) {
            $relais = $this->getCmd(null, 'relais'.$i);
            if (is_object($relais)) {
              $relais->remove();
              }
            }
          }

      // creation info entree
      if ($this->getConfiguration('entreesd') == 1) {
          for ($i=1; $i<9; $i++) {
              $entree = $this->getCmd(null, 'entree'.$i);
          		if (!is_object($entree)) {
          			$entree = new ipx800v4lnCmd();
          			$entree->setName(__('Entree '.$i, __FILE__));
          		}
          		$entree->setEqLogic_id($this->getId());
          		$entree->setLogicalId('entree'.$i);
          		$entree->setType('info');
          		$entree->setSubType('binary');
              $entree->setOrder(100);
              $entree->setIsHistorized(1);
          		$entree->save();
          }
      }
      else {
        for ($i=1; $i<=9; $i++) {
            $entree = $this->getCmd(null, 'entree'.$i);
            if (is_object($entree)) {
              $entree->remove();
              }
            }
          }

      // creation info entree analogique
      if ($this->getConfiguration('entreesa') == 1) {
          for ($i=1; $i<5; $i++) {
              $entreea = $this->getCmd(null, 'entreeA'.$i);
          		if (!is_object($entreea)) {
          			$entreea = new ipx800v4lnCmd();
          			$entreea->setName(__('Entree Analogique '.$i, __FILE__));
          		}
          		$entreea->setEqLogic_id($this->getId());
          		$entreea->setLogicalId('entreeA'.$i);
          		$entreea->setType('info');
          		$entreea->setSubType('numeric');
              $entreea->setOrder(100);
              $entreea->setIsHistorized(1);
          		$entreea->save();
          }
      }
      else {
        for ($i=1; $i<=9; $i++) {
            $entreea = $this->getCmd(null, 'entreeA'.$i);
            if (is_object($entreea)) {
              $entreea->remove();
              }
            }
          }

      // on ajoute un info de ping si besoin
      if ($this->getConfiguration('ping') == True) {
        log::add('ipx800v4ln', 'debug', "on teste le ping");
        // creation commande refresh
          $ping = $this->getCmd(null, 'presence');
      		if (!is_object($ping)) {
      			$ping = new snmp_clientCmd();
      			$ping->setName(__('Presence', __FILE__));
      		}
      		$ping->setEqLogic_id($this->getId());
      		$ping->setLogicalId('presence');
      		$ping->setType('info');
      		$ping->setSubType('binary');
          $ping->setOrder(200);
          $ping->setAlert('dangerif', '#value#=0');
          $ping->setIsHistorized(1);
      		$ping->save();
      	}
      else {
        $ping = $this->getCmd(null, 'presence');
            if (is_object($ping)) {
              $ping->setAlert('dangerif', '');
              $ping->remove();
            }
      }


    }


    public function preUpdate() {
      log::add('ipx800v4ln', 'debug', "preupdate");
      // on verifie au'il y a bien une ip de definie
      if ($this->getConfiguration('ip') == '') {
      			throw new Exception(__('L\'adresse IP ne peut etre vide', __FILE__));
      		}
    }

    public function postUpdate() {
    }

    public function preRemove() {

    }

    public function postRemove() {

    }

    /*     * **********************Getteur Setteur*************************** */
}

class ipx800v4lnCmd extends cmd {
    /*     * *************************Attributs****************************** */

    /*     * ***********************Methode static*************************** */

    /*     * *********************Methode d'instance************************* */

    public function execute($_options = array()) {
        $eqlogic = $this->getEqLogic(); //récupère l'éqlogic (l'equipement) de la commande $this
		    switch ($this->getLogicalId()) {	//vérifie le logicalid de la commande
			       case 'refresh': // LogicalId de la commande rafraîchir que l’on a créé dans la méthode Postsave de la classe  .
                   $etatRelais = $eqlogic->recupRelais();
                   $etatEntrees = $eqlogic->recupEntrees();
                   $etatEntreesAnalogiques = $eqlogic->recupEntreesAnalogiques();
                   for ($i=1; $i<9; $i++) {
                      $relais = $eqlogic->getCmd('info', 'relais'.$i);
                      log::add('ipx800v4ln', 'debug', "etat relais ".$i.":".$etatRelais[$i]);
                      $maj = $eqlogic->checkAndUpdateCmd($relais, $etatRelais[$i]);
                   }
                   for ($i=1; $i<9; $i++) {
                      $entree = $eqlogic->getCmd('info', 'entree'.$i);
                      log::add('ipx800v4ln', 'debug', "etat entree ".$i.":".$etatEntrees[$i]);
                      $maj = $eqlogic->checkAndUpdateCmd($entree, $etatEntrees[$i]);
                   }
                   for ($i=1; $i<5; $i++) {
                      $entreeA = $eqlogic->getCmd('info', 'entreeA'.$i);
                      log::add('ipx800v4ln', 'debug', "etat entree analogique".$i.":".$etatEntreesAnalogiques[$i]);
                      $maj = $eqlogic->checkAndUpdateCmd($entreeA, $etatEntreesAnalogiques[$i]);
                   }
                   if ($eqlogic->getConfiguration('ping') == True) {
                     $eqlogic->checkAndUpdateCmd('presence', $eqlogic->ping());
                   }
				           break;
		         }

    }

    /*     * **********************Getteur Setteur*************************** */
}
