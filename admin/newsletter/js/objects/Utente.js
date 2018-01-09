function Utente(o) {
	this.id_utente 	= parseInt(o.id);
	this.nome 		= o.nome;
	this.cognome 	= o.cognome;
	this.mail 		= o.mail;
	this.telefono 	= o.telefono;
	this.cellulare 	= o.cellulare;
	this.indirizzo 	= o.indirizzo;
	this.categoria 	= o.categoria;
	// -----
	
	this.timestamp	= parseInt(o.timestamp);
	// -----
};

Utente.prototype = {};
// -----