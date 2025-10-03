<div class="card">
    <div class="card-header">
        <h2>LGPD/GDPR Compliance</h2>
    </div>
    <div class="card-body">
        <h3 style="margin-bottom: 16px;">Cookie Consent</h3>
        <p>Código pronto para adicionar em seu site:</p>
        <div class="code-block" style="font-size: 11px;">
&lt;!-- Cookie Consent Banner --&gt;
&lt;div id="cookie-consent" style="position: fixed; bottom: 0; left: 0; right: 0; background: #1F2937; color: white; padding: 20px; z-index: 9999; display: none;"&gt;
    &lt;div style="max-width: 1200px; margin: 0 auto; display: flex; align-items: center; justify-content: space-between; gap: 20px;"&gt;
        &lt;p style="margin: 0;"&gt;Usamos cookies para melhorar sua experiência. &lt;a href="/privacy" style="color: #60A5FA;"&gt;Política de Privacidade&lt;/a&gt;&lt;/p&gt;
        &lt;button onclick="document.getElementById('cookie-consent').style.display='none'; localStorage.setItem('cookies-accepted', 'true');" style="padding: 10px 24px; background: #3B82F6; color: white; border: none; border-radius: 6px; cursor: pointer; white-space: nowrap;"&gt;Aceitar&lt;/button&gt;
    &lt;/div&gt;
&lt;/div&gt;
&lt;script&gt;
if (!localStorage.getItem('cookies-accepted')) {
    document.getElementById('cookie-consent').style.display = 'block';
}
&lt;/script&gt;
        </div>
        <button onclick="copyToClipboard(this.previousElementSibling.textContent)" class="btn btn-sm btn-primary" style="margin-top: 12px;">Copiar Código</button>
    </div>
</div>

<div class="card" style="margin-top: 20px;">
    <div class="card-header">
        <h2>Direitos dos Titulares</h2>
    </div>
    <div class="card-body">
        <a href="/lgpd-compliance.php" class="btn btn-primary">Acessar Painel Completo</a>
        <p style="margin-top: 16px; color: #6B7280;">
            Exportação de dados, exclusão (Right to Erasure), portabilidade e mais.
        </p>
    </div>
</div>





