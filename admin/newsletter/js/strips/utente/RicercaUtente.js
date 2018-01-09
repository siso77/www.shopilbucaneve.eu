var RicercaUtente = function(parent) {
	// parent
	this.parent = parent;
	// -----

	// xhr
	this.xhr_utente = this.parent._xhr_utente;
	// -----
	
	// initialize object
	this.init();
	// -----
};

RicercaUtente.prototype = {
	// -----
};

RicercaUtente.prototype.init = function() {
	var self = this;
	// -----
	
	self.toElements().inject($('ricerca_utente').empty());
	// -----
};

RicercaUtente.prototype.setFiltriRicerca = function(cognome, mail, categoria) {
	var self = this;
	// -----
	
	// verifica se è in esecuzione
	// un'altra richiesta ajax
	// con lo stesso componente
	if(self.xhr_utente.running) {
		_LOADER.html('<b>xhr_utente: another ajax request in progess...</b>');
		// -----
	}
	
	else {
		// set request's events
		self.xhr_utente.onSuccess = _onSuccess;
		// -----
		
		// post request
		self.xhr_utente.post({ 
			fase: 'setFiltriRicerca',
			cognome: cognome,
			mail: mail,
			categoria: categoria
		});
	}
	
	// event: _onSuccess
	function _onSuccess(text, xml) {
		_LOADER.clear();
		// -----
		
		// _ELENCO_UTENTE.load();
		self.parent.ElencoUtente.load();
		// -----
	};
};

RicercaUtente.prototype.azzeraFiltriRicerca = function() {
	var self = this;
	// -----
	
	// verifica se è in esecuzione
	// un'altra richiesta ajax
	// con lo stesso componente
	if(self.xhr_utente.running) {
		_LOADER.html('<b>xhr_utente: another ajax request in progess...</b>');
		// -----
	}
	
	else {
		// set request's events
		self.xhr_utente.onSuccess = _onSuccess;
		// -----
		
		// post request
		self.xhr_utente.post({ fase: 'azzeraFiltriRicerca' });
		// -----
	}
	
	// event: _onSuccess
	function _onSuccess(text, xml) {
		_LOADER.clear();
		// -----
		
		// _ELENCO_UTENTE.load();
		self.parent.ElencoUtente.load();
		// -----
	};
};

RicercaUtente.prototype.toElements = function() {
	var self = this;
	// -----
	
	var element = new Element('div', { style: 'border: 1px solid #999; background: #EEE;' });
	// -----
	
	var fieldset = new Element('fieldset', { style: 'border: 0px; padding: 5px; margin: 0px;' }).inject(element);
	var fieldset_table = new Element('table', { style: 'text-align: left;' }).inject(fieldset);
	var fieldset_table_tr = new Element('tr').inject(fieldset_table);
	var fieldset_table_th_cognome = new Element('th', { style: 'width: 160px; vertical-align: top;' }).set('html', 'Cognome').inject(fieldset_table_tr);
	var fieldset_table_th_mail = new Element('th', { style: 'width: 160px; vertical-align: top;' }).set('html', 'Mail').inject(fieldset_table_tr);
	var fieldset_table_th_privilegi = new Element('th', { style: 'width: 120px; vertical-align: top;' }).set('html', 'Categoria').inject(fieldset_table_tr);
	var fieldset_table_th_button = new Element('th').set('html', '&nbsp;').inject(fieldset_table_tr);
	// -----
	
	var fieldset_table_tr = new Element('tr').inject(fieldset_table);
	var fieldset_table_td_cognome = new Element('td', { style: 'vertical-align: top;' }).inject(fieldset_table_tr);
	var fieldset_table_td_cognome_input = new Element('input', { type: 'text', value: self.parent._filtro_cognome, style: 'width: 160px;' }).inject(fieldset_table_td_cognome);
	var fieldset_table_td_mail = new Element('td', { style: 'vertical-align: top;' }).inject(fieldset_table_tr);
	var fieldset_table_td_mail_input = new Element('input', { type: 'text', value: self.parent._filtro_mail, style: 'width: 160px;' }).inject(fieldset_table_td_mail);
	var fieldset_table_td_privilegi = new Element('td', { style: 'vertical-align: top;' }).inject(fieldset_table_tr);
	var fieldset_table_td_privilegi_select = new Element('select', { style: 'width: 120px;' }).inject(fieldset_table_td_privilegi);
	var fieldset_table_td_privilegi_select_option = new Element('option', { value: '' }).set('text', '-').inject(fieldset_table_td_privilegi_select);
	// -----
	
	// insert $privilegi options
	_UI._categorie.each(function(el) {
		var fieldset_table_td_privilegi_select_option = new Element('option', { value: el.categoria, selected: ((self.parent._filtro_categoria == el.categoria) ? (true) : (false)) }).set('text', el.categoria).inject(fieldset_table_td_privilegi_select);
		// -----
	});
	
	var fieldset_table_td_button = new Element('td', { style: 'vertical-align: top;' }).inject(fieldset_table_tr);
	var fieldset_table_td_button_set_filtri_ricerca = new Element('input', { type: 'button', value: 'Go' }).inject(fieldset_table_td_button);
	var fieldset_table_td_button_azzera_filtri_ricerca = new Element('input', { type: 'button', value: 'Azzera Ricerca' }).inject(fieldset_table_td_button);
	// -----

	// TODO: setFiltriRicerca
	fieldset_table_td_button_set_filtri_ricerca.addEvent('click', function(e) {
		self.setFiltriRicerca(fieldset_table_td_cognome_input.value,
							  fieldset_table_td_mail_input.value,
							  fieldset_table_td_privilegi_select.value);
		// -----
	});

	// TODO: azzeraFiltriRicerca
	fieldset_table_td_button_azzera_filtri_ricerca.addEvent('click', function(e) {
		fieldset_table_td_cognome_input.value = '';
		fieldset_table_td_mail_input.value = '';
		fieldset_table_td_privilegi_select.selectedIndex = 0;
		// -----
		
		self.azzeraFiltriRicerca();
		// -----
	});
	
	return element;
	// -----
};