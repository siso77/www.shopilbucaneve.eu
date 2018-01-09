var CreaUtente = function(parent) {
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

CreaUtente.prototype = {
	// -----
};

CreaUtente.prototype.init = function() {
	var self = this;
	// -----
	
	self.fetchElements();
	// -----
};


CreaUtente.prototype.fetchElements = function() {
	var self = this;
	// -----
	
	self.toElements().inject($('crea_utente').empty());
	// -----
};

CreaUtente.prototype.toElements = function() {
	var self = this;
	// -----
	
	var element = new Element('div', { style: 'border: 1px solid #999; background: #EEE;' });
	// -----
	
	// header
	var fieldset = new Element('fieldset', { style: 'border: 0px; padding: 5px; margin: 0px;' }).inject(element);
	var fieldset_table = new Element('table', { style: 'text-align: left;' }).inject(fieldset);
	var fieldset_table_tr = new Element('tr').inject(fieldset_table);
	var fieldset_table_th_username = new Element('th', { style: 'width: 160px;' }).set('html', 'Nome').inject(fieldset_table_tr);
	var fieldset_table_th_password = new Element('th', { style: 'width: 120px;' }).set('html', 'Cognome').inject(fieldset_table_tr);
	var fieldset_table_th_button = new Element('th').set('html', '&nbsp;').inject(fieldset_table_tr);
	// -----

	// form
	var fieldset_table_tr = new Element('tr').inject(fieldset_table);
	// -----
	
	// username
	var fieldset_table_td_nome = new Element('td', { style: 'vertical-align: top;' }).inject(fieldset_table_tr);
	var fieldset_table_td_nome_input = new Element('input', { type: 'text', style: 'width: 160px;' }).inject(fieldset_table_td_nome);
	// -----
	
	// password
	var fieldset_table_td_cognome = new Element('td', { style: 'vertical-align: top;' }).inject(fieldset_table_tr);
	var fieldset_table_td_cognome_input = new Element('input', { type: 'text', style: 'width: 120px;' }).inject(fieldset_table_td_cognome);
	// -----
		
	// button
	var fieldset_table_td_button = new Element('td', { style: 'vertical-align: top;' }).inject(fieldset_table_tr);
	var fieldset_table_td_button_save = new Element('input', { type: 'button', value: 'Go' }).inject(fieldset_table_td_button);
	// -----

	// save event
	fieldset_table_td_button_save.addEvent('click', function(e) {
		if(fieldset_table_td_nome_input.value.clean() == '') {
			alert('Devi inserire un Nome valido!'); return false;
			// -----
		}
		
		if(fieldset_table_td_cognome_input.value.clean() == '') {
			alert('Devi inserire un cognome valida!'); return false;
			// -----
		}
		
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
				fase: 'creaUtente',
				nome: fieldset_table_td_nome_input.value,
				cognome: fieldset_table_td_cognome_input.value
			});
		}
		
		// event: _onSuccess
		function _onSuccess(text, xml) {
			_LOADER.clear();
			// -----
			
			var response = JSON.decode(text);
			// -----

			switch(response.status) {
				default:
				case false: {
					// non è andato a buon fine
					// per "feedback" motivo
					alert(response.feedback);
					// -----
				}break;
				
				case true: {
					fieldset_table_td_nome_input.value = '';
					fieldset_table_td_cognome_input.value = '';
					// -----
					
					// self.parent.ElencoUtente.load();
					// -----
					
					self.parent.RicercaUtente.init();
					self.parent.RicercaUtente.azzeraFiltriRicerca();
					// -----
				}break;
			}
		};
	});
	
	return element;
	// -----
};

