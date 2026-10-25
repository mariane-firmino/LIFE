# Notificações por e-mail

As notificações internas são registradas para os servidores ativos. E-mails de consultas e triagens são opcionais e começam desativados; cada usuário pode ativá-los em **Configurações > Notificações**. E-mails de triagem são genéricos e não incluem nome de aluno nem respostas clínicas.

Eventos cobertos:

- novo cadastro de servidor aguardando aprovação: alerta interno para administradores ativos; e-mail opcional para administradores que habilitarem esse aviso;
- ativação ou desativação de conta: alerta interno e e-mail transacional para o servidor, independentemente das preferências opcionais;
- criação, alteração, reagendamento ou remoção de consulta: alerta interno e e-mail somente para quem ativou avisos de consulta;
- registro de triagem: alerta interno e e-mail somente para quem ativou avisos de triagem.

## Banco de dados

Execute `database/migrations/20261024_create_preferencia_notificacao.sql` no banco existente. A tabela também está incluída no dump `bd_life.sql` para novas instalações. As preferências são criadas sob demanda e têm padrão desativado.

## SMTP

O envio de notificações e o de recuperação de senha usam o mesmo `EmailSender`. Configure estas variáveis no ambiente do servidor web; não as armazene no repositório:

- `LIFE_SMTP_HOST` (opcional; padrão `smtp.gmail.com`)
- `LIFE_SMTP_PORT` (opcional; padrão `587`)
- `LIFE_SMTP_SECURE` (`tls` ou `ssl`; padrão `tls`)
- `LIFE_SMTP_USERNAME`
- `LIFE_SMTP_PASSWORD`
- `LIFE_SMTP_FROM` (opcional; padrão igual ao usuário SMTP)
- `LIFE_SMTP_FROM_NAME` (opcional; padrão `Enfermaria IFRO`)

Em XAMPP/Apache no Windows, configure-as fora do projeto, no ambiente do serviço Apache, e reinicie o Apache. Credenciais ausentes ou falhas de entrega são registradas no log do PHP e não desfazem a gravação da consulta, triagem ou alteração de conta.

Como uma credencial SMTP esteve armazenada diretamente no código, revogue-a e gere uma nova senha de aplicativo antes de configurar o ambiente.
