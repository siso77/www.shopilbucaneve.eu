var ElencoUtente = function(parent) {
	// parent
	this.parent = parent;
	// -----
	
	// xhr
	this.xhr_utente = this.parent._xhr_utente;
	// -----

	this.page = 1;
	this.pageTotal = 1;
	// -----
	
	this.record = 10;
	this.total = 0;
	// -----
	
	// dataset container of elements
	this.dataset = [];
	// -----

	this.init();
	// -----
};

ElencoUtente.prototype = {
	// -----	
};

ElencoUtente.prototype.init = function() {
	var self = this;
	// -----
	
	self.load();
	// -----
};

ElencoUtente.prototype.load = function() {
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
		self.xhr_utente.post({ fase: 'load', page: self.page, record: self.record });
		// -----
	}
	
	// event: _onSuccess
	function _onSuccess(text, xml) {
		_LOADER.clear();
		// -----
		
		self.fetchDataset(text);
		self.fetchElements();
		// -----
	};
};

ElencoUtente.prototype.fetchDataset = function(json) {
	var self = this;
	var dataset = JSON.decode(json);
	// -----

	// clean dataset
	self.total = dataset.count;
	self.dataset = [];
	// -----

	dataset.records.each(function(el) {
		self.dataset.push(el);
		// -----
	});
};

ElencoUtente.prototype.fetchElements = function() {
	var self = this;
	// -----
	
	self.toElements().inject($('elenco_utente').empty());
	// -----
};

ElencoUtente.prototype.paginazione = function() {
	var self = this;
	// -----
	
	var pages = [];
 	for(var p = 1; p <= Math.ceil(self.total/self.record); p++) {
 		pages.push(p);
 		// -----
 	}
	
	var element = new Element('div');
	// -----
	
	var element_left = new Element('div', { style: 'float: left;' }).inject(element);
	var element_right = new Element('div', { style: 'float: right;' }).inject(element);
	var element_block = new Element('div', { style: 'clear: both;' }).inject(element);
	// -----
	
	var paginazione_statistiche = new Element('div', { style: 'padding: 5px; text-align: left;' }).set('html', ' pagina <b>' + self.page + '</b> di <b>' + pages.length + '</b> - <b>' + self.total + '</b> record presenti').inject(element_left)
	// -----
	
	var paginazione_navigatore = new Element('div', { style: 'padding: 5px; text-align: right; font-size: 12px; ' }).inject(element_right);
	// -----

	// paginazione prev
	if(this.page > 1) {
		var pagina_prev = new Element('a', {'href': 'javascript:;'}).set('html', ' [prev] ').inject(paginazione_navigatore);
		// -----

		pagina_prev.addEvent('click', function(event) {
			event = new Event(event);
			event.stop();
			// -----

			self.page = self.page - 1;
			// -----

			self.load();
			// -----
		});
	}

	pages.each(function(n) {
		if(n == 1) {
			if(self.page == n) {
				var pagina = new Element('span', {}).set('html', ' <strong>' + n + '</strong> ').inject(paginazione_navigatore);
				// -----
				
				if(pages.length > 1) {
					var span = new Element('span').set('html', '|').inject(paginazione_navigatore);
					// -----
				}
			}
			
			else {
				var pagina = new Element('a', {'href': 'javascript:;'}).set('html', ' ' + n + ' ').inject(paginazione_navigatore);
				// -----
				
				if(self.page > 4) {
					var span = new Element('span').set('html', '| ... |').inject(paginazione_navigatore);
					// -----
				}
				
				else {
					var span = new Element('span').set('html', '|').inject(paginazione_navigatore);
					// -----
				}
			}
		}
		
		else if(n == pages.length && self.page != n) {
			if(self.page < pages.length - 3) {
				var span = new Element('span').set('html', ' ... |').inject(paginazione_navigatore);
				// -----
			}
			
			var pagina = new Element('a', {'href': 'javascript:;'}).set('html', ' ' + n + ' ').inject(paginazione_navigatore);
			// -----
		}

		else if(self.page == (n-2)) {
			var pagina = new Element('a', {'href': 'javascript:;'}).set('html', ' ' + n + ' ').inject(paginazione_navigatore);
			var span = new Element('span').set('html', '|').inject(paginazione_navigatore);
			// -----
		}

		else if(self.page == (n-1)) {
			var pagina = new Element('a', {'href': 'javascript:;'}).set('html', ' ' + n + ' ').inject(paginazione_navigatore);
			var span = new Element('span').set('html', '|').inject(paginazione_navigatore);
			// -----
		}

		else if(self.page == n) {
			var pagina = new Element('span').set('html', ' <strong>' + n + '</strong> ').inject(paginazione_navigatore);
			// -----
			
			if(self.page < pages.length) {
				var span = new Element('span').set('html', '|').inject(paginazione_navigatore);
				// -----
			}
		}

		else if(self.page == (n+1)) {
			var pagina = new Element('a', {'href': 'javascript:;'}).set('html', ' ' + n + ' ').inject(paginazione_navigatore);
			var span = new Element('span').set('html', '|').inject(paginazione_navigatore);
			// -----
		}

		else if(self.page == (n+2)) {
			var pagina = new Element('a', {'href': 'javascript:;'}).set('html', ' ' + n + ' ').inject(paginazione_navigatore);
			var span = new Element('span').set('html', '|').inject(paginazione_navigatore);
			// -----
		}

		if((n == 1) || (self.page == (n-2) || (self.page == (n-1)) || (self.page == n) || (self.page == (n+1)) || (self.page == (n+2)) || (n == pages.length))) {
			pagina.addEvent('click', function(event) {
				event = new Event(event);
				event.stop();
				// -----

				self.page = n;
				// -----

				self.load();
				// -----
			});
		}
	});

	// paginazione next
	if(self.page < pages.length) {
		var pagina_next = new Element('a', {'href': 'javascript:;'}).set('html', ' [next] ').inject(paginazione_navigatore);
		// -----

		pagina_next.addEvent('click', function(event) {
			event = new Event(event);
			event.stop();
			// -----

			self.page = self.page + 1;
			// -----

			self.load();
			// -----
		});
	};
	
	var paginazione_record = new Element('span', { style: 'font-size: 12px;' }).set('html', ' - record: ').inject(paginazione_navigatore);
	var paginazione_record_select = new Element('select', { style: 'font-size: 12px;' }).inject(paginazione_record);
	var paginazione_record_select_option = new Element('option', { value:   5, selected: ((self.record ==   5) ? (true) : (false)) }).set('text',   5).inject(paginazione_record_select);
	var paginazione_record_select_option = new Element('option', { value:  10, selected: ((self.record ==  10) ? (true) : (false)) }).set('text',  10).inject(paginazione_record_select);
	var paginazione_record_select_option = new Element('option', { value:  25, selected: ((self.record ==  25) ? (true) : (false)) }).set('text',  25).inject(paginazione_record_select);
	var paginazione_record_select_option = new Element('option', { value:  50, selected: ((self.record ==  50) ? (true) : (false)) }).set('text',  50).inject(paginazione_record_select);
	var paginazione_record_select_option = new Element('option', { value: 100, selected: ((self.record == 100) ? (true) : (false)) }).set('text', 100).inject(paginazione_record_select);
	// -----
	
	paginazione_record_select.addEvent('change', function(event) {
		event = new Event(event);
		event.stop();
		// -----

		self.page = 1;
		self.record = this.value;
		// -----

		self.load();
		// -----
	});
	
	return element;
	// -----
};

ElencoUtente.prototype.toElements = function() {
	var self = this;
	// -----
	
	var element = new Element('div', { style: 'border: 1px solid #999;' });
	// -----
	
	// insert paginazione
	self.paginazione().inject(element);
	// -----
	
	var fieldset = new Element('fieldset', { style: 'border: 0px; padding: 0px; margin: 0px;' }).inject(element);
	var fieldset_table = new Element('table', { style: 'width: 100%; text-align: left;' }).inject(fieldset);
	var fieldset_table_tr = new Element('tr', { style: 'color: #FFF; background: #333;' }).inject(fieldset_table);
	var fieldset_table_th_utente = new Element('th', { style: 'width: 410px; padding: 5px; font-size: 11px;' }).set('html', 'Persona').inject(fieldset_table_tr);
	var fieldset_table_th_privilegi = new Element('th', { style: 'width: 120px; padding: 5px; font-size: 11px;' }).set('html', 'Categoria').inject(fieldset_table_tr);
	var fieldset_table_th_spacer = new Element('th', { style: 'padding: 5px; font-size: 11px;' }).set('html', '&nbsp;').inject(fieldset_table_tr);
	var fieldset_table_th_button = new Element('th', { style: 'width: 120px; padding: 0px 5px; font-size: 11px; text-align: right;'}).inject(fieldset_table_tr);
	// -----
	
	// button: reload
	var fieldset_table_th_button_reload = new Element('input', { type: 'button', value: 'reload' }).inject(fieldset_table_th_button);
	// -----
	
	// action: edit selected
	fieldset_table_th_button_reload.addEvent('click', function(e) {
		self.load();
		// -----
	});

	self.dataset.each(function(el, i) {
		self.elementToRow(el, i).inject(fieldset_table);
		// -----
	});
	
	// insert paginazione
	self.paginazione().inject(element);
	// -----
	
	return element;
	// -----
};

ElencoUtente.prototype.elementToRow = function(el, i) {
	var self = this;
	// -----

	var el = new Utente(el);
	// -----
	
	// form
	var fieldset_table_tr = new Element('tr', { });
	
	// td: titolo, descrizione, copyright
	var fieldset_table_td = new Element('td', { style: 'vertical-align: top; padding: 5px; border-bottom: 2px dotted #DDD; ' }).inject(fieldset_table_tr);
	var fieldset_table_td_username_label = new Element('div', { }).set('html', '<a href="javascript:;" style="font-size: 12px; font-weight: bold;" class=\"tit-gallery\">' + el.nome + ' '+ el.cognome +'</a>').inject(fieldset_table_td);
	var fieldset_table_td_spacer_label = new Element('div', { class: 'descrizione', style: 'padding-bottom: 15px;' }).set('html', '&nbsp;').inject(fieldset_table_td);
	// -----
	
	var fieldset_table_td_data_elements = function(timestamp) {
		var date = new Date(parseInt(el.timestamp + '000'));
		// -----

		var html = '<b>Registrato il</b> ';
		html += ((('' + date.getDate()).length > 1) ? (date.getDate()) : ('0' + (date.getDate()))) + '/';
		html += ((('' + (date.getMonth()+1)).length > 1) ? (date.getMonth()+1) : ('0' + (date.getMonth()+1))) + '/';
		html += date.getFullYear() + ' <b>alle ore</b> ';
		html += ((('' + date.getHours()).length > 1) ? (date.getHours()) : ('0' + (date.getHours()))) + ':';
		html += ((('' + date.getMinutes()).length > 1) ? (date.getMinutes()) : ('0' + (date.getMinutes()))) + ':';
		html += ((('' + date.getSeconds()).length > 1) ? (date.getSeconds()) : ('0' + (date.getSeconds())));
		// -----
		
		var element = new Element('div', { style: 'color: #999; margin-top: 5px; padding-top: 5px; border-top: 1px dotted #ddd;' }).set('html', html);
		// -----
		
		return element;
		// -----
	};
	 
	// td: data
	var fieldset_table_td_telefoni_label = new Element('div', { style: '' }).set('html', '<b>Telefoni:</b> ' + ((el.telefono != '') ? ((el.cellulare != '') ? (el.telefono +' - '+ el.cellulare) : (el.telefono)) : ((el.cellulare != '') ? (el.cellulare) : ('-')))).inject(fieldset_table_td);
	var fieldset_table_td_indirizzo_label = new Element('div', { style: '' }).set('html', '<b>Indirizzo:</b> ' + ((el.indirizzo != '') ? (el.indirizzo) : ('-'))).inject(fieldset_table_td);

	var fieldset_table_td_email_label = new Element('div', { style: '' }).set('html', '<b>E-mail:</b> ' + ((el.mail != '') ? (el.mail) : ('-'))).inject(fieldset_table_td);
//	var fieldset_table_td_register_label = new Element('div', { style: '' }).set('html', '<b>Register:</b> ' + ((el.register != '') ? (el.register) : ('-'))).inject(fieldset_table_td);
	var fieldset_table_td_data_label = fieldset_table_td_data_elements().inject(fieldset_table_td);
	// -----
	
//	var fieldset_table_td_privilegi_elements = function() {
//		var element = new Element('td', { style: 'vertical-align: top; border-bottom: 2px dotted #DDD; ' });
//		// -----
//		
//		switch(el.privilegi) {
//			case 1: {
//				var privilegi = new Element('div', {}).inject(element);
//				var privilegi_img = new Element('div', { style: 'float: left; margin-top: 1px;' }).set('html', '<img src="/images/ico/user_guest.png" border="0" />').inject(privilegi);
//				var privilegi_label = new Element('div', { style: 'float: left; margin-left: 5px;' }).set('html', 'guest').inject(privilegi);
//				var privilegi_br = new Element('br', { style: 'clear: both;' }).inject(privilegi);
//				// -----
//			}break;
//			
//			case 2: {
//				var privilegi = new Element('div', {}).inject(element);
//				var privilegi_img = new Element('div', { style: 'float: left; margin-top: 1px;' }).set('html', '<img src="/images/ico/user_editor.png" border="0" />').inject(privilegi);
//				var privilegi_label = new Element('div', { style: 'float: left; margin-left: 5px;' }).set('html', 'editor').inject(privilegi);
//				var privilegi_br = new Element('br', { style: 'clear: both;' }).inject(privilegi);
//				// -----
//			}break;
//			
//			case 3: {
//				var privilegi = new Element('div', {}).inject(element);
//				var privilegi_img = new Element('div', { style: 'float: left; margin-top: 1px;' }).set('html', '<img src="/images/ico/user_master_editor.png" border="0" />').inject(privilegi);
//				var privilegi_label = new Element('div', { style: 'float: left; margin-left: 5px;' }).set('html', 'master editor').inject(privilegi);
//				var privilegi_br = new Element('br', { style: 'clear: both;' }).inject(privilegi);
//				// -----
//			}break;
//			
//			case 4: {
//				var privilegi = new Element('div', {}).inject(element);
//				var privilegi_img = new Element('div', { style: 'float: left; margin-top: 1px;' }).set('html', '<img src="/images/ico/user_admin.png" border="0" />').inject(privilegi);
//				var privilegi_label = new Element('div', { style: 'float: left; margin-left: 5px;' }).set('html', 'admin').inject(privilegi);
//				var privilegi_br = new Element('br', { style: 'clear: both;' }).inject(privilegi);
//				// -----
//			}break;
//		}
//		
//		return element;
//		// -----
//	};
//	
//	// td: telefono
	var fieldset_table_td_telefono = new Element('td', { style: 'vertical-align: top; padding: 5px; border-bottom: 2px dotted #DDD;' }).set('html', '&nbsp;').inject(fieldset_table_tr);
	var fieldset_table_td_telefono_label = new Element('div', { style: '' }).set('html', '' + ((el.categoria != '') ? (el.categoria) : ('-'))).inject(fieldset_table_td_telefono);
	// -----
	
	var fieldset_table_td_spacer = new Element('td', { style: 'vertical-align: top; padding: 5px; border-bottom: 2px dotted #DDD;' }).set('html', '&nbsp;').inject(fieldset_table_tr);
	// -----
	
	// td: delete
	var fieldset_table_td = new Element('td', { style: 'vertical-align: top; padding: 5px; border-bottom: 2px dotted #DDD; text-align: right;' }).inject(fieldset_table_tr);
	var fieldset_table_td_button_edit_input = new Element('input', { type: 'button', value: 'edit' }).inject(fieldset_table_td);
	var fieldset_table_td_button_delete_input = new Element('input', { type: 'button', value: 'delete' }).inject(fieldset_table_td);
	// -----
	
	// event: edit
	fieldset_table_td_button_edit_input.addEvent('click', function(e) {
		self.elementToRowEdit(self.dataset[i], i).replaces(fieldset_table_tr);
		// -----
	});
	
	// event: delete
	fieldset_table_td_button_delete_input.addEvent('click', function(e) {
		if(!confirm('Sei sicuro di voler eliminare l\'utente selezionato?\n')) return false;
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
				fase: 'eliminaUtente',
				id: el.id_utente
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
					self.load();
					// -----
				}break;
			}
		};
	});
	
	return fieldset_table_tr;
	// -----
};

ElencoUtente.prototype.elementToRowEdit = function(el, i) {
	var self = this;
	var timer = null;
	// -----

	// reload element
	var el = new Utente(el);
	// -----
	
	// tr
	var fieldset_table_tr = new Element('tr', { style: 'background: #EEE;' });
	
	// td: titolo, descrizione, copyright
	var fieldset_table_td = new Element('td', { style: 'vertical-align: top; padding: 5px; border-bottom: 2px dotted #999;' }).inject(fieldset_table_tr);
	var fieldset_table_td_nome_label = new Element('div', { style: 'padding-bottom: 5px;' }).set('html', '<div style="font-weight: bold;">Nome:</div> ').inject(fieldset_table_td);
	var fieldset_table_td_nome_input = new Element('input', { type: 'text', value: el.nome, style: 'width: 150px;' }).inject(fieldset_table_td_nome_label);	
	var fieldset_table_td_cognome_label = new Element('div', { style: 'padding-bottom: 5px;' }).set('html', '<div style="font-weight: bold;">Cognome:</div> ').inject(fieldset_table_td);
	var fieldset_table_td_cognome_input = new Element('input', { type: 'text', value: el.cognome, style: 'width: 150px;' }).inject(fieldset_table_td_cognome_label);	
	var fieldset_table_td_mail_label = new Element('div', { style: 'padding-bottom: 5px;' }).set('html', '<div style="font-weight: bold;">E-Mail:</div> ').inject(fieldset_table_td);
	var fieldset_table_td_mail_input = new Element('input', { type: 'text', value: el.mail, style: 'width: 150px;' }).inject(fieldset_table_td_mail_label);	
	var fieldset_table_td_telefono_label = new Element('div', { style: 'padding-bottom: 5px;' }).set('html', '<div style="font-weight: bold;">Telefono:</div> ').inject(fieldset_table_td);
	var fieldset_table_td_telefono_input = new Element('input', { type: 'text', value: el.telefono, style: 'width: 150px;' }).inject(fieldset_table_td_telefono_label);	
	var fieldset_table_td_cellulare_label = new Element('div', { style: 'padding-bottom: 5px;' }).set('html', '<div style="font-weight: bold;">Cellulare:</div> ').inject(fieldset_table_td);
	var fieldset_table_td_cellulare_input = new Element('input', { type: 'text', value: el.cellulare, style: 'width: 150px;' }).inject(fieldset_table_td_cellulare_label);	
	var fieldset_table_td_indirizzo_label = new Element('div', { style: 'padding-bottom: 5px;' }).set('html', '<div style="font-weight: bold;">Indirizzo:</div> ').inject(fieldset_table_td);
	var fieldset_table_td_indirizzo_input = new Element('input', { type: 'text', value: el.indirizzo, style: 'width: 150px;' }).inject(fieldset_table_td_indirizzo_label);	
	// -----
	
	// td: categoria
	var fieldset_table_td = new Element('td', { style: 'vertical-align: top; padding: 5px; border-bottom: 2px dotted #999; ' }).inject(fieldset_table_tr);
	var fieldset_table_td_categoria_select = new Element('select', { style: 'width: 120px;' }).inject(fieldset_table_td);
	var fieldset_table_td_categoria_select_option = new Element('option', { value: '' }).set('text', '-').inject(fieldset_table_td_categoria_select);
	// -----
	
	// insert $privilegi options
	_UI._categorie.each(function(p) {
		var fieldset_table_td_categoria_select_option = new Element('option', { value: p.categoria, selected: ((p.categoria == el.categoria) ? (true) : (false)) }).set('text', p.categoria).inject(fieldset_table_td_categoria_select);
		// -----
	});

	var fieldset_table_td_spacer = new Element('td', { style: 'vertical-align: top; padding: 5px; border-bottom: 2px dotted #999;' }).set('html', '&nbsp;').inject(fieldset_table_tr);
	// -----
	
	// change event for all elements
	fieldset_table_td_nome_input.addEvent('change', function(e) { el.nome = this.value; });
	fieldset_table_td_cognome_input.addEvent('change', function(e) { el.cognome = this.value; });
	fieldset_table_td_mail_input.addEvent('change', function(e) { el.mail = this.value; });
	fieldset_table_td_telefono_input.addEvent('change', function(e) { el.telefono = this.value; });
	fieldset_table_td_cellulare_input.addEvent('change', function(e) { el.cellulare = this.value; });
	fieldset_table_td_indirizzo_input.addEvent('change', function(e) { el.indirizzo = this.value; });
	fieldset_table_td_categoria_select.addEvent('change', function(e) { el.categoria = this.value; });
	// -----
	
	var fieldset_table_td_button = new Element('td', { style: 'vertical-align: top; padding: 5px; border-bottom: 2px dotted #999; text-align: right;' }).inject(fieldset_table_tr);
	var fieldset_table_td_button_save_input = new Element('input', { type: 'button', value: 'salva' }).inject(fieldset_table_td_button);
	var fieldset_table_td_button_cancel_input = new Element('input', { type: 'button', value: 'annulla' }).inject(fieldset_table_td_button);
	// -----
	
	// save event
	fieldset_table_td_button_save_input.addEvent('click', function(e) {
		// var button_save_input = this;
		// button_save_input.disabled = true;
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
				fase: 'modificaUtente',
				id: el.id_utente,
				nome: el.nome,
				cognome: el.cognome,
				mail: el.mail,
				telefono: el.telefono,
				cellulare: el.cellulare,
				indirizzo: el.indirizzo,
				categoria: el.categoria
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
					// save modified row version
					self.dataset[i] = el;
					// -----
					
					self.elementToRow(self.dataset[i], i).replaces(fieldset_table_tr);
					// -----
				}break;
			}
		};
	});
	
	// cancel event
	fieldset_table_td_button_cancel_input.addEvent('click', function(e) {
		self.elementToRow(self.dataset[i], i).replaces(fieldset_table_tr);
		// -----
	});

	return fieldset_table_tr;
	// -----
};
