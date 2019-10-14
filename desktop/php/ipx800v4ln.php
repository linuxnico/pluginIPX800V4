<?php
if (!isConnect('admin')) {
	throw new Exception('{{401 - Accès non autorisé}}');
}
$plugin = plugin::byId('ipx800v4ln');
sendVarToJS('eqType', $plugin->getId());
$eqLogics = eqLogic::byType($plugin->getId());
?>

<div class="row row-overflow">
   <div class="col-xs-12 eqLogicThumbnailDisplay">
  <legend><i class="fas fa-cog"></i>  {{Gestion}}</legend>
  <div class="eqLogicThumbnailContainer">
      <div class="cursor eqLogicAction logoPrimary" data-action="add">
        <i class="fas fa-plus-circle" style="color:#4b71af;"></i>
        <br>
        <span style="color:#4b71af;">{{Ajouter}}</span>
    </div>
      <div class="cursor eqLogicAction logoSecondary" data-action="gotoPluginConf">
      <i class="fas fa-wrench" style="color:#c8c8c8;"></i>
    <br>
    <span style="color:#c8c8c8;">{{Configuration}}</span>
  </div>
  </div>
  <legend><i class="fas fa-table"></i> {{Mes equipements}}</legend>
	   <input class="form-control" placeholder="{{Rechercher}}" id="in_searchEqlogic" />
<div class="eqLogicThumbnailContainer">
    <?php
foreach ($eqLogics as $eqLogic) {
	$opacity = ($eqLogic->getIsEnable()) ? '' : 'disableCard';
	echo '<div class="eqLogicDisplayCard cursor '.$opacity.'" data-eqLogic_id="' . $eqLogic->getId() . '">';
	echo '<img src="' . $plugin->getPathImgIcon() . '"/>';
	echo '<br>';
	echo '<span class="name">' . $eqLogic->getHumanName(true, true) . '</span>';
	echo '</div>';
}
?>
</div>
</div>

<div class="col-xs-12 eqLogic" style="display: none;">
		<div class="input-group pull-right" style="display:inline-flex">
			<span class="input-group-btn">
				<a class="btn btn-default btn-sm eqLogicAction roundedLeft" data-action="configure"><i class="fa fa-cogs"></i> {{Configuration avancée}}</a><a class="btn btn-default btn-sm eqLogicAction" data-action="copy"><i class="fas fa-copy"></i> {{Dupliquer}}</a><a class="btn btn-sm btn-success eqLogicAction" data-action="save"><i class="fas fa-check-circle"></i> {{Sauvegarder}}</a><a class="btn btn-danger btn-sm eqLogicAction roundedRight" data-action="remove"><i class="fas fa-minus-circle"></i> {{Supprimer}}</a>
			</span>
		</div>
  <ul class="nav nav-tabs" role="tablist">
    <li role="presentation"><a href="#" class="eqLogicAction" aria-controls="home" role="tab" data-toggle="tab" data-action="returnToThumbnailDisplay"><i class="fa fa-arrow-circle-left"></i></a></li>
    <li role="presentation" class="active"><a href="#eqlogictab" aria-controls="home" role="tab" data-toggle="tab"><i class="fa fa-tachometer"></i> {{Equipement}}</a></li>
    <li role="presentation"><a href="#commandtab" aria-controls="profile" role="tab" data-toggle="tab"><i class="fa fa-list-alt"></i> {{Commandes}}</a></li>
  </ul>
  <div class="tab-content" style="height:calc(100% - 50px);overflow:auto;overflow-x: hidden;">
    <div role="tabpanel" class="tab-pane active" id="eqlogictab">
      <br/>
    <form class="form-horizontal">
        <fieldset>
            <div class="form-group">
                <label class="col-sm-3 control-label">{{Nom de l'équipement}}</label>
                <div class="col-sm-3">
                    <input type="text" class="eqLogicAttr form-control" data-l1key="id" style="display : none;" />
                    <input type="text" class="eqLogicAttr form-control" data-l1key="name" placeholder="{{Nom de l'équipement template}}"/>
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-3 control-label" >{{Objet parent}}</label>
                <div class="col-sm-3">
                    <select id="sel_object" class="eqLogicAttr form-control" data-l1key="object_id">
                        <option value="">{{Aucun}}</option>
                        <?php
foreach (object::all() as $object) {
	echo '<option value="' . $object->getId() . '">' . $object->getName() . '</option>';
}
?>
                   </select>
               </div>
           </div>
	   <div class="form-group">
                <label class="col-sm-3 control-label">{{Catégorie}}</label>
                <div class="col-sm-9">
                 <?php
                    foreach (jeedom::getConfiguration('eqLogic:category') as $key => $value) {
                    echo '<label class="checkbox-inline">';
                    echo '<input type="checkbox" class="eqLogicAttr" data-l1key="category" data-l2key="' . $key . '" />' . $value['name'];
                    echo '</label>';
                    }
                  ?>
               </div>
           </div>
	<div class="form-group">
		<label class="col-sm-3 control-label"></label>
		<div class="col-sm-9">
			<label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="isEnable" checked/>{{Activer}}</label>
			<label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="isVisible" checked/>{{Visible}}</label>
		</div>
	</div>
	<div class="form-group">
	 <label class="col-sm-3 control-label">{{IP}}</label>
	 <div class="col-sm-3">
			 <input type="text" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="ip" placeholder="{{adresse IP}}"/>
	 </div>
	</div>
	<div class="form-group">
	 <label class="col-sm-3 control-label">{{Superviser les Entrees Digitales}}</label>
	 <div class="col-sm-3">
			 <input type="checkbox" class="eqLogicAttr" data-l1key="configuration" data-l2key="entreesd" placeholder="{{superviser}}"/>
	 </div>
	</div>
	<div class="form-group">
	 <label class="col-sm-3 control-label">{{Superviser les Entrees Analogiques}}</label>
	 <div class="col-sm-3">
			 <input type="checkbox" class="eqLogicAttr" data-l1key="configuration" data-l2key="entreesa" placeholder="{{superviser}}"/>
	 </div>
	</div>
	<div class="form-group">
	 <label class="col-sm-3 control-label">{{Superviser les Sorties a Relais}}</label>
	 <div class="col-sm-3">
			 <input type="checkbox" class="eqLogicAttr" data-l1key="configuration" data-l2key="relais" placeholder="{{superviser}}"/>
	 </div>
	</div>
	<div class="form-group">
			<label class="col-sm-3 control-label">{{test PING:}}</label>
			<div class="col-sm-3">
					<input type="checkbox" class="eqLogicAttr" data-l1key="configuration" data-l2key="ping" placeholder="ping"/>{{fait un test de presence sur reseau ip}}
			</div>
	</div>
	<div class="form-group">
	 <label class="col-sm-3 control-label">{{Clef de l'API}}</label>
	 <div class="col-sm-3">
			 <input type="text" class="eqLogicAttr" data-l1key="configuration" data-l2key="apikey"  placeholder="{{clef de l'api de l'ipx800v4}}"/>
	 </div>
	</div>
		<div class="form-group">
      <label class="col-sm-3 control-label">{{Auto-actualisation (cron)}}</label>
      <div class="col-sm-2">
        <input type="text" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="autorefresh" placeholder="{{Auto-actualisation (cron)}}"/>
      </div>
      <div class="col-sm-1">
        <i class="fa fa-question-circle cursor floatright" id="bt_cronGenerator"></i>
      </div>
    </div>
		<div class="form-group">
      <label class="col-sm-3 control-label">{{adresse de PUSH pour ipx800}}</label>
      <div class="col-sm-7">
        <?php
				echo network::getNetworkAccess('internal')."/core/api/jeeApi.php?plugin=ipx800v4ln&apikey=".jeedom::getApiKey('ipx800v4ln')."&type=cmd&id=#ID#&value=#VALEUR#";
				?>
				<br><br>
				{{Les champs #ID# et #VALEUR# sont a remplacer p[ar l'id de la commande et les valeurs 0 ou 1}}
      </div>
    </div>

</fieldset>
</form>
</div>
      <div role="tabpanel" class="tab-pane" id="commandtab">
<a class="btn btn-success btn-sm cmdAction pull-right" data-action="add" style="margin-top:5px;"><i class="fa fa-plus-circle"></i> {{Commandes}}</a><br/><br/>
<table id="table_cmd" class="table table-bordered table-condensed">
    <thead>
        <tr>
            <th>{{Id}}</th><th>{{Nom}}</th><th>{{Type}}</th><th>{{Unite}}</th><th>{{Etat}}</th><th>{{Action}}</th>
        </tr>
    </thead>
    <tbody>
    </tbody>
</table>
</div>
</div>

</div>
</div>

<?php include_file('desktop', ipx800v4ln, 'js', ipx800v4ln);?>
<?php include_file('core', 'plugin.template', 'js');?>