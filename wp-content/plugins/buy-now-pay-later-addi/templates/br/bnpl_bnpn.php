<?php
?>
<div class="addi-description__container4x">
        <?php if($discount): ?>
            <div class="addi-header-discount --4x">
                  <div class="addi-header-discount__badge">
                    <svg class="addi-header-discount__icon" width="18" height="15" viewBox="0 0 18 15" fill="none" xmlns="http://www.w3.org/2000/svg">
                      <path fill-rule="evenodd" clip-rule="evenodd" d="M1 0.5C0.447716 0.5 0 0.947715 0 1.5V4.5C0 5.05228 0.474524 5.47927 0.958973 5.74447C1.57933 6.08406 2 6.74289 2 7.5C2 8.25711 1.57933 8.91594 0.958974 9.25553C0.474524 9.52073 0 9.94771 0 10.5V13.5C0 14.0523 0.447715 14.5 1 14.5H17C17.5523 14.5 18 14.0523 18 13.5V10.5C18 9.94771 17.5255 9.52073 17.041 9.25553C16.4207 8.91594 16 8.25711 16 7.5C16 6.74289 16.4207 6.08406 17.041 5.74447C17.5255 5.47927 18 5.05228 18 4.5V1.5C18 0.947715 17.5523 0.5 17 0.5H1ZM8.632 5.52554C8.632 6.75283 7.84374 7.57103 6.79604 7.57103C5.76831 7.57103 5 6.74286 5 5.54549C5 4.3182 5.77829 3.5 6.816 3.5C7.85371 3.5 8.632 4.3182 8.632 5.52554ZM7.44462 10.6443H6.37697L8.92136 6.91248L11.0766 3.61974H12.1542L9.58989 7.35152L7.44462 10.6443ZM6.80602 4.44791C6.35701 4.44791 6.07763 4.87697 6.07763 5.52554C6.07763 6.15415 6.35701 6.60316 6.82598 6.60316C7.27499 6.60316 7.55437 6.17411 7.55437 5.53552C7.55437 4.89692 7.28497 4.44791 6.80602 4.44791ZM9.90919 8.73846C9.90919 7.52114 10.6775 6.69297 11.7152 6.69297C12.7629 6.69297 13.5312 7.51116 13.5312 8.7185C13.5312 9.9458 12.7429 10.764 11.7052 10.764C10.6675 10.764 9.90919 9.9458 9.90919 8.73846ZM11.7252 9.80611C12.1742 9.80611 12.4636 9.37705 12.4636 8.73846C12.4636 8.08989 12.1842 7.64088 11.7152 7.64088C11.2662 7.64088 10.9768 8.07991 10.9768 8.7185C10.9768 9.3571 11.2562 9.80611 11.7252 9.80611Z" fill="#FFF"/>
                    </svg>
                    <span class="addi-header-discount__label">$<?php echo ($discount*100); ?>% de desconto</span>
                  </div>
                </div>
        <?php endif; ?>
          <div class="addi-description-info">
            <div class="addi-description-info__logo">
              <svg width="83" height="32" viewBox="0 0 83 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path fill-rule="evenodd" clip-rule="evenodd" d="M75.1647 30.5814H81.8178V11.0417H75.1647V30.5814Z" fill="#4E7EFF""/>
                <path fill-rule="evenodd" clip-rule="evenodd" d="M82.7624 4.40475C82.7624 2.04593 80.8502 0.133789 78.4914 0.133789C76.1325 0.133789 74.2202 2.04593 74.2202 4.40475C74.2202 6.76358 76.1325 8.67572 78.4914 8.67572C80.8502 8.67572 82.7624 6.76358 82.7624 4.40475Z" fill="#4E7EFF""/>
                <path fill-rule="evenodd" clip-rule="evenodd" d="M60.9584 24.4984C58.407 24.4984 56.3384 22.43 56.3384 19.8785C56.3384 17.3271 58.407 15.2588 60.9584 15.2588C63.5099 15.2588 65.5783 17.3271 65.5783 19.8785C65.5783 22.43 63.5099 24.4984 60.9584 24.4984ZM72.2483 19.8785V1.24609H65.5783V9.93843C64.3146 9.12732 62.605 8.67578 60.9584 8.67578C54.7232 8.67578 49.6686 13.6914 49.6686 19.8785C49.6686 26.0657 54.7232 31.0815 60.9584 31.0815C66.9871 31.0815 72.2483 26.4441 72.2483 19.8785Z" fill="#4E7EFF""/>
                <path fill-rule="evenodd" clip-rule="evenodd" d="M36.1939 24.4984C33.6424 24.4984 31.574 22.43 31.574 19.8785C31.574 17.3271 33.6424 15.2588 36.1939 15.2588C38.7454 15.2588 40.8137 17.3271 40.8137 19.8785C40.8137 22.43 38.7454 24.4984 36.1939 24.4984ZM47.4837 19.8785V1.24609H40.8137V9.93843C39.5501 9.12732 37.8405 8.67578 36.1939 8.67578C29.9587 8.67578 24.9041 13.6914 24.9041 19.8785C24.9041 26.0657 29.9587 31.0815 36.1939 31.0815C42.2226 31.0815 47.4837 26.4441 47.4837 19.8785Z" fill="#4E7EFF""/>
                <path fill-rule="evenodd" clip-rule="evenodd" d="M11.2898 14.3772C9.17757 14.3772 7.37591 15.7035 6.66981 17.5684V11.3635C6.66981 8.81201 8.73821 6.74364 11.2898 6.74364C13.8413 6.74364 15.9097 8.81201 15.9097 11.3635V17.5684C15.2037 15.7035 13.402 14.3772 11.2898 14.3772ZM15.9097 21.0813V30.4771H22.5628V11.8113C22.5687 11.6621 22.5796 11.5141 22.5796 11.3635C22.5796 5.17641 17.525 0.160645 11.2898 0.160645C5.05457 0.160645 0 5.17641 0 11.3635V30.4771H6.65309V21.0217C7.34756 22.9091 9.16129 24.2554 11.2898 24.2554C13.3714 24.2554 15.1518 22.9677 15.8788 21.1453L15.9097 21.0813Z" fill="#4E7EFF""/>
              </svg>
            </div>
            <div class="addi-description-info-text">
              <p class="addi-description-info-text__label4x">
                Você parcela suas compras em 1+3x no 
                <strong class="addi-title__strong">Pix, sem juros ou cartão!</strong>
              </p>
            </div>
          </div>
          
          <div class="addi-payment__container">
            <div class="addi-payment__header">
              <div >
                <p class="addi-payment-price">R$<?php echo $installments; ?></p>
                <p class="addi-step-inactive__name">Cada mês</p>
              </div>
        
              <div class="addi-payment__badge">
                <span class="addi-payment__label">1+3x sem juros!</span>
              </div>
            </div>

          <div class="addi-step-parcelas selector">
            <div class="addi-step__container selector">
              <div class="addi-step-active__number">1</div>
              <div class="addi-step">
                <p class="addi-step-active__name">hoje</p>
                <p class="addi-step__price">R$<?php echo $installments; ?></p>
              </div>
            </div>
            <div class="addi-step__container selector">
              <div class="addi-step-inactive__number two">2</div>
              <div class="addi-step">
                <p class="addi-step-inactive__name">em 1 mês</p>
                <p class="addi-step__price">R$<?php echo $installments; ?></p>
              </div>
            </div>
            <div class="addi-step__container selector">
              <div class="addi-step-inactive__number three">3</div>
              <div class="addi-step">
                <p class="addi-step-inactive__name">em 2 meses</p>
                <p class="addi-step__price">R$<?php echo $installments; ?></p>
              </div>
            </div>
            <div class="addi-step__container selector">
              <div class="addi-step-inactive__number four">4</div>
              <div class="addi-step">
                <p class="addi-step-inactive__name">em 3 meses</p>
                <p class="addi-step__price">R$<?php echo $installments; ?></p>
              </div>
            </div>
          </div>

          </div>
          <div>
              <p class="addi-info-message addi-internal-message">
                  <svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                      <path d="M6.99992 13.6667C3.31802 13.6667 0.333252 10.6819 0.333252 7.00004C0.333252 3.31814 3.31802 0.333374 6.99992 0.333374C10.6818 0.333374 13.6666 3.31814 13.6666 7.00004C13.6625 10.6803 10.6801 13.6627 6.99992 13.6667ZM1.66659 7.11471C1.69813 10.0489 4.09399 12.4064 7.02828 12.3907C9.96257 12.3749 12.3329 9.9917 12.3329 7.05737C12.3329 4.12304 9.96257 1.73989 7.02828 1.72404C4.09399 1.70835 1.69813 4.06588 1.66659 7.00004V7.11471ZM8.33325 10.3334H6.33325V7.66671H5.66659V6.33337H7.66659V9.00004H8.33325V10.3334ZM7.66659 5.00004H6.33325V3.66671H7.66659V5.00004Z" fill="#3C6AF0"/>
                  </svg>
                    A primeira parcela será paga hoje
              </p>
          </div>
        
          <div class="addi-payment__container">
            <div class="addi-payment__header">
              <div >
                <p class="addi-payment-price">R$<?php echo number_format($total, 2); ?></p>
                <p class="addi-step-inactive__name">Pagamento único</p>
              </div>
        
              <div class="addi-payment__badge">
                <span class="addi-payment__label">Pix à vista</span>
              </div>
            </div>
            <p class="addi-payment__description">Pagamento total com Pix. É rápido e mais seguro!</p>
          </div>

        <?php if($min_amount && $total < intval($min_amount_int)): ?>
            <div class='constraint-container'>Disponível somente para compras acima de R$<?php echo $min_amount; ?></div>
        <?php endif; ?>

        <?php if($max_amount && $total> intval($max_amount_int)): ?>
            <div class='constraint-container'>Disponível somente para compras abaixo de R$<?php echo $max_amount; ?></div>
        <?php endif; ?>

          <p class="addi-description__paragraph">
            Clique em
            <strong class="addi-title__strong">"Pagar com Addi" </strong>
            para concluir.
          </p>
        </div>