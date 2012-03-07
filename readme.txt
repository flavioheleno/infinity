*****************************************
*	infinity|Framework					*
*****************************************
*	http://bit.ly/infinity-framework	*
*****************************************

---------------------------------
 Estrutura de diretórios
---------------------------------
./app:				arquivos da aplicação do usuário (models, views e controllers)
./cache:			pasta que armazena os arquivos de cachê gerados pelo driver filecache
./cfg:				pasta que armazena os arquivos de configuração do sistema
./cfg/app:			arquivos de configuração da aplicação do usuário
./cfg/core:			arquivos de configuração do framework (acesso a base de dados, mensagens, etc)
./cfg/form:			arquivos de configuração dos formulários usados pela aplicação
./core:				arquivos do framework (não edite a não ser que saiba o que está fazendo)
./css:				pasta que armazena os arquivos de cascading style sheet (aplicação + framework)
./examples:			aplicações de exemplo
./img:				arquivos de imagem da aplicação do usuário
./js:				pasta que armazena os arquivos de javascript (aplicação + framework)
./mail:				arquivos de template para email
./plugin:			classes criadas pelo usuário, disponíveis para toda a aplicação
./tpl:				pasta que armazena os templates da aplicação
./tpl/cache:		pasta que armazena os arquivos de cachê dos templates
./worker:			pasta que armazena os workers

---------------------------------
 Helpers disponíveis
---------------------------------
MODEL
	query:			abstração para acesso ao mysql
	validator:		validação de dados baseado em regras
	secure:			criptografia e decriptografia de dados

VIEW
	html:			gera arquivos html válidos
	template:		manipulação de templates
	form:			gera formulários html com validação baseada em jquery
	url:			controla a criação de urls

CONTROLLER
	session:		controle total sobre a sessão do usuário
	email:			envio de e-mails via smtp
	privilege:		controle de privilégio
	cookie:			manipulação de cookies
	filecache:		cachê em disco
	mcache:			cachê em memória (baseado em memcache)
	xcache:			cachê em memória (baseado em xcache)

SISTEMA
	log:			criação de log de sistema
	data:			compartilhamento de dados entre model/view/controller
	configuration:	manipulação dos arquivos de configuração
	path:			resolução de diretórios

---------------------------------
 Instalação
---------------------------------
A instalação do framework pode ser feita através do script "setup.php" disponível na raíz.
Para executá-lo digite em um terminal: 'php -f setup.php' e veja as opções disponíveis.

---------------------------------
 Dependências
---------------------------------
O framework depende das seguintes bibliotecas/módulos:
 - HTML_Template_Sigma (disponível em pear.php.net)
 - SwiftMailer (disponível em swiftmailer.org)

O framework pode depender dos seguintes módulos/servidores:
 - php5-memcache + memcached (para habilitar o driver memcache do sistema de cachê)
 - php5-xcache (para habilitar o driver xcache do sistema de cachê)
