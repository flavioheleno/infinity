*********************************
*	infinity|Framework			*
*********************************
*	infinity.versatil.eti.br	*
*********************************

Estrutura de diretórios
---------------------------------
./app:				arquivos da aplicação do usuário (models, views e controllers)
./cfg:				pasta que armazena os arquivos de configuração do sistema
./cfg/app:			arquivos de configuração da aplicação do usuário
./cfg/core:			arquivos de configuração do framework (acesso a base de dados, rotas, mensagens)
./core:				arquivos do framework (não edite a não ser que saiba o que está fazendo)
./css:				pasta que armazena os arquivos de cascading style sheet
./css/app:			arquivos de cascading style sheet da aplicação do usuário
./css/core:			arquivos de cascading style sheet do framework (não edite a não ser que saiba o que está fazendo)
./img:				arquivos de imagem da aplicação do usuário
./js:				pasta que armazena os arquivos de javascript
./js/app:			arquivos de javascript da aplicação do usuário
./js/core:			arquivos de javascript do framework (não edite a não ser que saiba o que está fazendo)
./tpl:				pasta que armazena os templates do sistema
./tpl/cache:		pasta que armazena os arquivos de cachê dos templates
./tpl/app:			templates da aplicação do usuário
./tpl/app/default:	páginas comuns (padrão) da aplicação
./tpl/core:			templates do framework (não edite a não ser que saiba o que está fazendo)

Helpers disponíveis
---------------------------------
MODEL
	sql:			abstração para acesso ao mysql
	activerecord:	active record baseado na abstração sql
	validator:		validação de dados baseado em regras

VIEW
	xhtml:			gera arquivos xhtml válidos
	template:		manipulação de templates
	form:			gera formulários html com validação baseada em jquery
	msg:			gera mensagens baseadas nas configurações

CONTROLLER
	session:		controle total sobre a sessão do usuário
	email:			envio de e-mails via smtp
	secure:			criptografia e descriptografia de dados
	cache:			cache de dados
