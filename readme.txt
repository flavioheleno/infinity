*****************************************
*	infinity|Framework					*
*****************************************
*	http://bit.ly/infinity-framework	*
*****************************************

Estrutura de diretórios
---------------------------------
./app:				arquivos da aplicação do usuário (models, views e controllers)
./cfg:				pasta que armazena os arquivos de configuração do sistema
./cfg/app:			arquivos de configuração da aplicação do usuário
./cfg/core:			arquivos de configuração do framework (acesso a base de dados, mensagens, etc)
./cfg/form:			arquivos de configuração dos formulários usados pela aplicação
./core:				arquivos do framework (não edite a não ser que saiba o que está fazendo)
./css:				pasta que armazena os arquivos de cascading style sheet
./css/app:			arquivos de cascading style sheet da aplicação do usuário
./css/core:			arquivos de cascading style sheet do framework (não edite a não ser que saiba o que está fazendo)
./examples:			aplicações de exemplo
./img:				arquivos de imagem da aplicação do usuário
./js:				pasta que armazena os arquivos de javascript
./js/app:			arquivos de javascript da aplicação do usuário
./js/core:			arquivos de javascript do framework (não edite a não ser que saiba o que está fazendo)
./tpl:				pasta que armazena os templates da aplicação
./tpl/cache:		pasta que armazena os arquivos de cachê dos templates

Helpers disponíveis
---------------------------------
MODEL
	query:			abstração para acesso ao mysql
	validator:		validação de dados baseado em regras
	secure:			criptografia e decriptografia de dados

VIEW
	xhtml:			gera arquivos xhtml válidos
	template:		manipulação de templates
	form:			gera formulários html com validação baseada em jquery
	msg:			gera mensagens baseadas nas configurações
	cache:			cache de dados baseado em arquivo com controle de timeout
	url:			controla a criação de urls

CONTROLLER
	session:		controle total sobre a sessão do usuário
	email:			envio de e-mails via smtp
	privilege:		controle de privilégio

SISTEMA
	log:			criação de log de sistema
	data:			compartilhamento de dados entre model/view/controller
