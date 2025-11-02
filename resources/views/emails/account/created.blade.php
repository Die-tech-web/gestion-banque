<x-mail::message>
# Bienvenue à {{ $bankName }} !

Bonjour {{ $client->user->name }},

Votre compte a été créé avec succès chez {{ $bankName }}.

Voici vos informations de connexion :
- **Email :** {{ $client->email }}
- **Mot de passe :** {{ $password }}
- **Code d'authentification :** {{ $codeAuthentification }}

Veuillez changer votre mot de passe lors de votre première connexion pour des raisons de sécurité.

Si vous avez des questions, n'hésitez pas à nous contacter.

Cordialement,
L'équipe {{ $bankName }}
</x-mail::message>
