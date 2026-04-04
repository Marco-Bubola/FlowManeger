# Guia de Equipes e Portal do Cliente

## 1. Visão geral

O sistema agora tem duas frentes principais:

1. Equipes internas para compartilhar dados entre usuários do sistema.
2. Portal do cliente para o próprio cliente acompanhar compras, ver produtos e pedir orçamentos.

## 2. Como funciona a área de Equipes

A área de equipes fica em:

- Configurações > Equipe

Nessa área o usuário pode:

1. Criar uma equipe.
2. Convidar outros usuários do sistema pelo e-mail deles.
3. Definir quais módulos a equipe compartilha.
4. Ajustar permissões individuais de cada membro.
5. Transferir dados entre usuários da mesma equipe.

## 3. Lógica de compartilhamento da equipe

Os módulos disponíveis hoje são:

1. Produtos
2. Clientes
3. Vendas
4. Financeiro

Cada módulo pode ser compartilhado ou não.

Exemplo:

1. Se Compartilhar Produtos estiver ativo, os membros autorizados enxergam os produtos dos usuários ligados à equipe.
2. Se Compartilhar Financeiro estiver desligado, ninguém da equipe verá esse módulo compartilhado.

## 4. Lógica de permissões dos membros

Além de ligar ou desligar o compartilhamento por módulo, cada membro tem permissões próprias.

Na prática, o controle acontece em dois níveis:

1. Nível da equipe: o módulo precisa estar compartilhado.
2. Nível do membro: o usuário precisa ter permissão para aquele acesso.

Ou seja:

1. Se a equipe compartilha Vendas, mas o membro não tem acesso a vendas, ele não verá as vendas.
2. Se o membro tiver permissão, mas o módulo da equipe estiver desligado, ele também não verá.

## 5. Como usar Equipes no dia a dia

### Criar a equipe

1. Entrar em Configurações > Equipe.
2. Informar nome e descrição.
3. Salvar.

### Convidar um membro

1. Na mesma área, preencher o e-mail do usuário do sistema.
2. Escolher o papel.
3. Marcar as permissões desejadas.
4. Enviar convite.

### Aceite do convite

1. O outro usuário entra na conta dele.
2. Vai em Configurações > Equipe.
3. Aceita o convite pendente.

### Ajustar acesso depois

1. Voltar na lista de usuários conectados.
2. Alterar papel e permissões.
3. Salvar.

## 6. Onde os acessos estão no sistema

### Equipes

1. Navegação das configurações: Configurações > Equipe.
2. Rotas da equipe estão registradas e funcionando.

### Portal do cliente

1. Lista de clientes.
2. Resumo do cliente.
3. Dashboard do cliente.
4. Área administrativa de orçamentos do cliente.
5. Sidebar própria do portal do cliente.

## 7. Como funciona a área do cliente

O portal do cliente foi feito para o cliente acessar diretamente com login próprio.

Ele consegue:

1. Ver as compras dele.
2. Ver o catálogo de produtos em estoque do vendedor.
3. Solicitar orçamento de produtos disponíveis.
4. Adicionar itens extras no orçamento.
5. Ver respostas do vendedor.
6. Aprovar ou recusar orçamento respondido.
7. Completar e atualizar o próprio cadastro.

## 8. Como enviar acesso ao cliente

O acesso pode ser enviado a partir destas telas:

1. Lista de clientes
2. Resumo do cliente
3. Dashboard do cliente

Regras:

1. O cliente precisa ter e-mail cadastrado.
2. Ao clicar em Enviar Acesso, o sistema gera uma senha temporária automaticamente.
3. Essa senha é salva com segurança no banco usando hash.
4. O acesso do cliente é ativado.
5. O sistema marca que ele é obrigado a trocar a senha no primeiro login.

## 9. Como pegar a senha do cliente para enviar

O fluxo é este:

1. Clique em Enviar Acesso.
2. O sistema tenta enviar o e-mail automaticamente para o cliente.
3. Se o e-mail for enviado com sucesso, o cliente recebe:
   - login: e-mail dele
   - senha temporária gerada pelo sistema
4. Se o e-mail falhar, o sistema mostra a senha temporária na mensagem de retorno para você copiar e mandar manualmente.

## 10. Como mandar para o cliente

Você pode mandar assim:

1. Link do portal
2. E-mail do cliente
3. Senha temporária

Exemplo de mensagem:

```text
Olá, seu acesso ao portal foi liberado.

Link: /portal/login
Login: seu-email@cliente.com
Senha temporária: XXXXXXXX

No primeiro acesso, o sistema vai pedir para você trocar a senha e completar seus dados cadastrais.
```

Se quiser, depois eu posso criar um botão que copie essa mensagem pronta automaticamente.

Atualização:

1. Esse botão já foi criado.
2. Depois de clicar em Enviar Acesso, o sistema mostra uma mensagem pronta para WhatsApp.
3. Você pode copiar e enviar diretamente ao cliente.

## 10.1 Onde copiar a mensagem pronta

A mensagem pronta pode aparecer logo após o envio do acesso nestas áreas:

1. Lista de clientes
2. Resumo do cliente
3. Dashboard do cliente

Ela já vem com:

1. Link do portal
2. Login do cliente
3. Senha temporária
4. Instrução de primeiro acesso

## 11. Lógica do login do cliente

A lógica atual está correta e ficou assim:

1. O cliente entra com e-mail e senha temporária.
2. O sistema valida o login no guard separado do portal.
3. Se o acesso estiver desativado, ele é bloqueado.
4. Se for primeiro acesso, ele não entra direto no dashboard.
5. Ele é redirecionado obrigatoriamente para Meu Perfil.
6. Em Meu Perfil, ele precisa:
   - definir uma nova senha
   - preencher os dados obrigatórios
7. Só depois disso o acesso completo ao portal é liberado.

## 12. Dados obrigatórios no primeiro acesso

Hoje o cliente precisa preencher:

1. Telefone
2. CPF/CNPJ
3. CEP
4. Rua
5. Número
6. Bairro
7. Cidade
8. Estado
9. Nova senha

Campos complementares disponíveis:

1. Complemento
2. Data de nascimento
3. Empresa
4. Observações pessoais

## 13. Lógica do CEP

Ao informar o CEP no perfil:

1. O portal consulta automaticamente o ViaCEP.
2. Se o CEP for válido, ele preenche:
   - rua
   - bairro
   - cidade
   - estado
3. O cliente só precisa completar o número e, se quiser, o complemento.

## 14. Endereço completo

O sistema também monta o campo de endereço completo automaticamente com base em:

1. Rua
2. Número
3. Complemento
4. Bairro
5. Cidade
6. Estado
7. CEP

Isso deixa a base pronta tanto para visualização quanto para futuras integrações.

## 15. Fluxo completo do portal do cliente

### Fluxo do vendedor

1. Cadastra o cliente com dados básicos.
2. Clica em Enviar Acesso.
3. O sistema gera senha temporária.
4. O cliente recebe os dados de acesso.

### Fluxo do cliente

1. Acessa o portal.
2. Faz login com e-mail e senha temporária.
3. É obrigado a trocar a senha.
4. Preenche os dados obrigatórios.
5. Passa a usar normalmente:
   - compras
   - produtos
   - orçamentos
   - perfil

## 16. Fluxo de orçamento do cliente

1. O cliente entra no catálogo.
2. Seleciona produtos com estoque.
3. Informa quantidades e observações.
4. Pode adicionar itens extras fora do catálogo.
5. Envia o orçamento.
6. O vendedor responde na área administrativa do cliente.
7. O cliente vê a resposta no portal.
8. Se o orçamento estiver respondido, ele pode aprovar ou recusar.

## 17. Segurança aplicada

1. Guard separado para cliente, sem misturar com o login interno da equipe/admin.
2. Senha do cliente salva com hash.
3. Rate limit no login do cliente.
4. Acesso desativado bloqueia login.
5. Primeiro acesso exige troca de senha.
6. Cliente só vê os próprios dados.
7. Dono do cliente ou admin controla o envio e revogação do acesso.

## 17.1 Recuperação de senha do cliente

Agora o cliente também pode recuperar o acesso sem depender do vendedor.

Fluxo:

1. Na tela de login do portal, o cliente clica em Esqueci minha senha.
2. Informa o e-mail dele.
3. O sistema envia um link de redefinição.
4. O cliente cria uma nova senha.
5. Depois volta a entrar normalmente no portal.

Rotas do fluxo:

1. Solicitação do reset
2. Envio do e-mail com token
3. Tela de redefinição da nova senha
4. Salvamento da nova senha

No ambiente local atual:

1. O APP_URL está apontando para o ngrok.
2. O MAIL_MAILER está configurado como log.
3. Por isso, o link real de redefinição fica registrado em storage/logs/laravel.log.

## 17.2 Login com Google do cliente

O portal também pode receber login com Google.

Regras:

1. O cliente clica em Entrar com Google na tela do portal.
2. O Google devolve o e-mail autenticado.
3. O sistema só libera a entrada se esse e-mail for exatamente o mesmo do cliente cadastrado.
4. No primeiro uso, o sistema salva o google_id do cliente.
5. Se o cliente ainda estiver em primeiro acesso, ele continua sendo enviado para Meu Perfil para completar o onboarding.

Configuração necessária:

1. Liberar a callback do portal no Google Cloud.
2. Definir GOOGLE_PORTAL_REDIRECT_URI com /portal/auth/google/callback.
3. Se quiser, usar a mesma credencial Google já existente do login interno.

## 18. O que já está validado

Foi validado:

1. Rotas do portal do cliente.
2. Rotas da equipe.
3. Views Blade.
4. Migrations.
5. Entradas nas áreas principais de clientes.
6. Sidebar do portal do cliente.

## 19. Como operar sem erro

### Para equipes

1. Sempre criar a equipe primeiro.
2. Depois ligar os módulos compartilhados.
3. Depois convidar membros.
4. Depois ajustar permissões individuais.

### Para clientes

1. Sempre cadastrar o e-mail do cliente antes de enviar acesso.
2. Depois clicar em Enviar Acesso.
3. Se o e-mail não sair, copiar a senha temporária mostrada pelo sistema.
4. Orientar o cliente a fazer o primeiro acesso e completar o cadastro.

## 20. Próximas melhorias recomendadas

Se quiser evoluir ainda mais, os próximos passos mais úteis são:

1. Criar notificação para o vendedor quando o cliente enviar orçamento.
2. Adicionar aprovação do orçamento gerando venda automaticamente.
3. Mostrar avatar do Google no topo do portal quando o cliente entrar por Google.
4. Criar histórico administrativo dos envios de acesso e resets do cliente.
5. Expandir a validação documental para algoritmo completo de CPF/CNPJ, além da máscara e do tamanho.
