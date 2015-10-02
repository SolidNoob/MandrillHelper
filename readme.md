<h1>MandrillHelper</h1>
<p>Une class Helper pour simplifier l'utilisation de l'API Mandrill.</p>

<br/>

<p>Exemple d'utilisation:</p>

```php
include "Mandrill.php";
 
$mail = new Mandrill();
 
// Destinataire principal (le deuxième paramètre est optionnel)
$mail->to('toto@yopmail.com', 'Toto');
 
// Les copies (optionnel)
$mail->cc('cc@yopmail.com')->bcc('bcc@yopmail.com');
 
// L'expediteur (optionnel) : par défaut la valeur de Mandrill::$default_from
$mail->from('from@yopmail.com', 'From');
 
// Le contenu en html
$mail->html('<p>Le contenu du message</p>');
 
// Le sujet
$mail->subject('Le message de test');
 
// Les pieces jointes
$mail->addAttachment("/chemin/vers/la/pj.png");
 
if(!$mail->send())
{
    echo "Une erreur s'est produite";
}else{
    echo 'Mail envoyé' ;
}
```