<?php
 
class Mandrill{
 
	// L'url sur laquelle faire les requêtes
	private $uri = 'https://mandrillapp.com/api/1.0/messages/send.json';
 
	// La clé d'API mandrill
	private $api_key = "cle-d-api";
 
	// Le contenu à envoyer
	private $message;
 
	// L'expediteur utilisé si from() n'est pas appelé
	private $default_from = array('from@par-defaut.com', 'From Defaut');
 
	// Les erreurs rencontrées lors de l'envoi du mail
	private $errors = array();
 
	public function __construct()
	{
		$this->message = new stdClass;
	}
 
	/**
	 * L'expediteur du mail
	 */
	public function from($mail, $nom = "")
	{
		$this->message->from_email = $mail;
		$this->message->from_name =  $nom;
 
		return $this;
	}
 
	/**
	 * L'adresse à laquelle répondre
	 * Par défaut : Le from
	 */
	public function replyTo($mail)
	{
		return $this->addHeader($mail, 'Reply-To');
	}
 
	/**
	 * Ajout d'un destinataire principal
	 */
	public function to($mail, $nom = "")
	{
		return $this->addAddress($mail, $nom, 'to');
	}
 
	/**
	 * Ajout d'un destinataire en copie
	 */
	public function cc($mail, $nom = "")
	{
		return $this->addAddress($mail, $nom, 'cc');
	}
 
	/**
	 * Ajout d'un destinataire caché
	 */
	public function bcc($mail, $nom = "")
	{
		return $this->addAddress($mail, $nom, 'bcc');
	}
 
	/**
	 * Ajout d'une adresse au message
	 */
	public function addAddress($mail, $nom, $type)
	{
		if(!in_array($type, ['to', 'cc', 'bcc'])) throw new Exception("Type de destinataire invalide : '$type'");
 
		// Init du tableau d'adresse
		if(!isset($this->message->to)) $this->message->to = array();
 
		// Ajout de l'adresse
		array_push($this->message->to, array('email' => $mail, 'name' => $nom, 'type' => $type));
 
		return $this;
	}
 
	/**
	 * Ajout du corps du message en html
	 */
	public function html($html)
	{
		$this->message->html = $html;
 
		return $this;
	}
 
	/**
	 * Ajout du corps du message en format text
	 * Inutile si la fonction html a été appelée
	 */
	public function text($text)
	{
		$this->message->text = $text;
 
		return $this;
	}
 
	/**
	 * Ajoute un sujet
	 */
	public function subject($sujet)
	{
		$this->message->subject = $sujet;
 
		return $this;
	}
 
	/**
	 * Ajoute des headers au mail
	 */
	public function addHeader($value, $type)
	{
		if(!isset($this->message->headers)) $this->message->headers = array();
 
		$this->message->headers[$type] = $value;
 
		return $this;
	}
 
	public function addAttachment($file, $name = "")
	{
		if(!@is_file($file)) throw new Exception("Fichier introuvable : '$file'");
 
		if(!isset($this->message->attachments)) $this->message->attachments = array();
 
		// Pas de nom : on prend le nom du fichier
		if($name == "")
		{
			$parts_file = explode(PHP_OS == "Windows" || PHP_OS == "WINNT" ? "\\" : "/" , $file);
			$name = end($parts_file);
		}
 
		$this->message->attachments[] = array('content' => base64_encode(file_get_contents($file)), 'name' => $name, 'type' => $this->getMimeType($file));
 
		return $this;
	}
 
	/**
	 * Détecte le mime type d'un fichier
	 */
	private function getMimeType($file)
	{
		if(!function_exists('finfo_file')) return mime_content_type($file);
 
		$finfo     = finfo_open(FILEINFO_MIME_TYPE);
		$mime_type = finfo_file($finfo, $file);
		finfo_close($finfo);
 
		return $mime_type;
	}
 
	/**
	 * Envoi de la requête
	 */
	public function send()
	{
		// Si besoin : mise en place du from
		if(!isset($this->message->from_email)) $this->from($this->default_from[0], $this->default_from[1]);
 
		// Le contenu de la requête
		$request = new stdClass;
		$request->message = $this->message;
		$request->async   = false;
		$request->key     = $this->api_key;
 
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $this->uri);
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true );
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true );
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($request));
		$result = json_decode(curl_exec($curl));
 
		// Retourne true si aucune erreur, false sinon
		// TODO à améliorer...
		return is_array($result);
	}
}