# 📱 Como Conectar WhatsApp via QR Code - Guia Completo

## 🎯 Visão Geral

A integração WhatsApp permite que você rastreie automaticamente todas as conversas recebidas no WhatsApp do seu negócio, atribuindo-as aos leads corretos e gerando análises completas de atendimento.

---

## 📋 Pré-requisitos

### 1. Conta BSP (Business Solution Provider)

Você precisa de uma conta em um provedor BSP oficial do WhatsApp:

**Opção 1: 360Dialog** (Recomendado)
- 🌐 Site: https://www.360dialog.com/
- ✅ Fácil configuração
- ✅ QR Code nativo
- ✅ Preço competitivo
- 📧 Criar conta: https://hub.360dialog.com/signup

**Opção 2: Infobip**
- 🌐 Site: https://www.infobip.com/whatsapp
- ✅ Recursos avançados
- ✅ Suporte empresarial

**Opção 3: Twilio**
- 🌐 Site: https://www.twilio.com/whatsapp
- ✅ API robusta
- ⚠️ Aprovação pode demorar

### 2. Número de Telefone

- ✅ Número de telefone dedicado para WhatsApp Business
- ✅ Não pode ser número já usado em WhatsApp pessoal
- ✅ Precisa receber SMS para verificação (na configuração do BSP)

---

## 🚀 Passo a Passo - Conectar WhatsApp

### **Passo 1: Obter Credenciais do BSP**

#### Para 360Dialog:

1. Acesse https://hub.360dialog.com/
2. Faça login na sua conta
3. Vá em **API Keys** no menu lateral
4. Clique em **Create New API Key**
5. Copie a API Key gerada
6. **Guarde em local seguro** - você precisará dela

```
Exemplo de API Key:
fGt7Ks9...mN4pQ2x (60+ caracteres)
```

#### Para Infobip:

1. Acesse https://portal.infobip.com/
2. Vá em **Account > API Keys**
3. Crie nova API Key com permissões WhatsApp
4. Copie Base URL e API Key

---

### **Passo 2: Conectar no VisionMetrics**

1. **Acesse o Dashboard**
   - Faça login no VisionMetrics
   - Vá em: **Integrações > WhatsApp**

2. **Clique em "Conectar WhatsApp (QR)"**
   - Botão azul no canto superior direito

3. **Preencha os Dados**
   - **Provedor BSP**: Selecione `360Dialog` (ou seu provedor)
   - **API Key**: Cole a API Key copiada do BSP
   - **Nome da Integração**: (Opcional) Ex: "WhatsApp Principal"

4. **Clique em "Gerar QR Code"**
   - Aguarde 5-10 segundos
   - QR Code aparecerá na tela

---

### **Passo 3: Escanear QR Code com WhatsApp**

1. **Abra WhatsApp no seu Celular**
   - Você precisa ter o WhatsApp Business instalado
   - Use o número que você registrou no BSP

2. **Acesse Dispositivos Vinculados**
   - **Android**: Toque em ⋮ (3 pontos) > Dispositivos vinculados
   - **iPhone**: Toque em Configurações > Dispositivos vinculados

3. **Vincular Dispositivo**
   - Toque em "Vincular um dispositivo"
   - Aponte a câmera para o QR Code na tela

4. **Aguarde Confirmação**
   - ⏳ Status mudará de "Aguardando..." para "Conectado!"
   - ✅ Você verá a mensagem de sucesso
   - 🎉 Pronto! WhatsApp conectado

---

## 📊 Como Funciona a Atribuição

### **1. Via vm_token (Recomendado)**

Quando um lead clica em um **link rastreável** do VisionMetrics e depois envia mensagem no WhatsApp mencionando o token:

```
Exemplo de mensagem do cliente:
"Olá! Vim pelo link vm_token:a1b2c3d4-e5f6-7890-abcd-ef1234567890"
```

✅ O sistema **automaticamente**:
- Identifica o lead pelo token
- Associa a conversa ao lead correto
- Atribui origem da campanha (UTM)
- Registra jornada completa

### **2. Via Número de Telefone**

Se não houver `vm_token`, o sistema tenta:
- Buscar lead existente pelo número de telefone
- Se encontrar: associa conversa
- Se não encontrar: cria lead anônimo

### **3. Lead Anônimo**

- Se nenhuma correspondência for encontrada
- Cria novo lead com número de telefone
- Você pode enriquecer depois manualmente

---

## 🔗 Integração com Links Rastreáveis

### **Como Criar Links que Funcionam com WhatsApp**

1. **Vá em Campanhas > Links Rastreáveis**

2. **Crie Novo Link**
   - **Nome**: Ex: "Promoção WhatsApp"
   - **URL Destino**: https://seusite.com/produto
   - **UTM Source**: `whatsapp`
   - **UTM Medium**: `organic` ou `campaign`
   - **UTM Campaign**: `promo-maio-2025`

3. **Copie o Link Curto**
   - Exemplo: `visionmetricsapp.com.br/l/abc123`

4. **Use no WhatsApp**
   - Cole no status
   - Envie em grupos
   - Compartilhe com clientes

5. **Quando Cliente Clicar**
   - Cookie `vm_first_touch` é salvo no navegador
   - Token UUID é gerado (Ex: `a1b2c3d4-...`)

6. **Cliente Manda Mensagem**
   - Instrua para incluir: "vim pelo link"
   - Sistema detecta token automaticamente
   - ✅ Atribuição completa!

---

## 📱 Visualizar Conversas

### **Acessar Conversas WhatsApp**

1. **Menu Lateral > WhatsApp > Conversas**
   - Ou acesse diretamente: `/backend/whatsapp/conversations.php`

2. **Buscar Conversas**
   - Por número de telefone
   - Por nome do lead
   - Por conteúdo da mensagem

3. **Clicar em uma Conversa**
   - Ver histórico completo
   - Ver dados do lead
   - Acessar perfil do lead

---

## 🛠️ Troubleshooting

### **QR Code não aparece**

❌ **Problema**: Tela fica em "Gerando QR Code..."

✅ **Soluções**:
1. Verifique se API Key está correta
2. Verifique conectividade com BSP
3. Abra Console do navegador (F12) e veja erros
4. Tente outro provedor BSP

---

### **QR Code aparece mas não conecta**

❌ **Problema**: Escaneei mas status não muda para "Conectado"

✅ **Soluções**:
1. Aguarde até 30 segundos
2. Verifique se está usando WhatsApp Business (não pessoal)
3. Certifique-se que o número é o mesmo registrado no BSP
4. Tente gerar novo QR Code

---

### **Mensagens não aparecem**

❌ **Problema**: WhatsApp conectado mas mensagens não chegam

✅ **Soluções**:
1. Verifique webhook do BSP:
   - 360Dialog: Configure `https://seudominio.com/webhooks/whatsapp.php`
2. Veja logs em: `logs/integrations.log`
3. Verifique `webhooks_logs` table no banco
4. Certifique-se que workspace está ativo

---

### **Atribuição não funciona**

❌ **Problema**: Mensagens chegam mas não associam ao lead

✅ **Soluções**:
1. **Para vm_token**: Cliente precisa mencionar token na mensagem
2. **Para telefone**: Número precisa estar cadastrado exatamente igual
3. Verifique formato do telefone (E.164): `+5511999999999`
4. Crie lead manualmente e associe telefone

---

## 🎓 Melhores Práticas

### **1. Configure Mensagem Automática**

No WhatsApp Business, configure mensagem de boas-vindas:

```
Olá! 👋 Obrigado por entrar em contato.

Se você veio através de um link nosso, 
por favor inclua o código que apareceu 
na página para melhor atendimento.

Aguarde que já vamos te responder!
```

### **2. Use Links Rastreáveis Sempre**

- Em posts de redes sociais
- Em anúncios
- Em e-mails
- Em status do WhatsApp
- Em bio do Instagram

### **3. Monitore Regularmente**

- Acesse WhatsApp > Conversas diariamente
- Responda mensagens pendentes
- Enriqueça leads anônimos
- Analise métricas de atendimento

### **4. Treine Sua Equipe**

- Explique importância do vm_token
- Mostre como buscar conversas
- Ensine atribuição manual se necessário

---

## 🔐 Segurança

### **Credenciais Encriptadas**

✅ Todas as API Keys são armazenadas com **AES-256-GCM encryption**
✅ Ninguém (nem admins) consegue ver credenciais em texto claro
✅ Chave de encriptação (`INTEGRATIONS_KEY`) deve estar no `.env`

### **Workspace Isolation**

✅ Cada workspace só vê suas próprias conversas
✅ Webhooks são roteados automaticamente para workspace correto
✅ Impossível ver conversas de outros clientes

---

## 📞 Suporte

### **Precisa de Ajuda?**

- 📧 Email: suporte@visionmetricsapp.com.br
- 💬 WhatsApp: (Link do suporte)
- 📚 Documentação: https://docs.visionmetricsapp.com.br

### **Recursos Úteis**

- 360Dialog Docs: https://docs.360dialog.com/
- WhatsApp Business API: https://developers.facebook.com/docs/whatsapp
- VisionMetrics YouTube: (Link dos tutoriais)

---

## ✅ Checklist Final

- [ ] Conta BSP criada e aprovada
- [ ] API Key obtida do BSP
- [ ] QR Code gerado no VisionMetrics
- [ ] WhatsApp escaneado e conectado
- [ ] Webhook configurado no BSP
- [ ] Primeira mensagem teste recebida
- [ ] Conversa aparece em WhatsApp > Conversas
- [ ] Link rastreável criado e testado
- [ ] Atribuição via vm_token testada

---

**🎉 Parabéns! Seu WhatsApp está totalmente integrado ao VisionMetrics!**

Agora todas as suas conversas WhatsApp serão rastreadas, atribuídas e analisadas automaticamente. 📊✨




