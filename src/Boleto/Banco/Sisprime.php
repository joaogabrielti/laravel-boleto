<?php
namespace Eduardokum\LaravelBoleto\Boleto\Banco;

use Eduardokum\LaravelBoleto\Boleto\AbstractBoleto;
use Eduardokum\LaravelBoleto\CalculoDV;
use Eduardokum\LaravelBoleto\Contracts\Boleto\Boleto as BoletoContract;
use Eduardokum\LaravelBoleto\Contracts\Boleto\Boleto;
use Eduardokum\LaravelBoleto\Util;

class Sisprime extends AbstractBoleto implements BoletoContract
{
    /**
     * Código do banco
     *
     * @var string
     */
    protected $codigoBanco = Boleto::COD_BANCO_SISPRIME;
    /**
     * Define as carteiras disponíveis para este banco
     * '09' => Carteira de Cobrança
     *
     * @var array
     */
    protected $carteiras = ['09'];
    /**
     * Variaveis adicionais.
     *
     * @var array
     */
    public $variaveis_adicionais = [];
    /**
     * Espécie do documento, código para remessa
     *
     * @var string
     */
    protected $especiesCodigo240 = [
        'DM' => '02', // Duplicata Mercantil
        // 'DM' => '03', // Duplicata Mercantil
        'DS' => '04', // Duplicata de Serviço
        // 'DS' => '05', // Duplicata de Serviço
        'LC' => '07', // Letras de Câmbio
        'NP' => '12', // Nota Promissória
        // 'NP' => '13', // Nota Promissória
        'NS' => '16', // Nota de Seguro
        'RE' => '17', // Recibo
        'ND' => '19', // Nota de Débito
        'O'  => '99', // Outros,
    ];
    /**
     * Espécie do documento, código para remessa
     *
     * @var string
     */
    protected $especiesCodigo400 = [
        // TODO: Implementar espécies do documento para o layout CNAB 400.
    ];
    /**
     * Mostrar o endereço do beneficiário abaixo da razão e CNPJ na ficha de compensação
     *
     * @var boolean
     */
    protected $mostrarEnderecoFichaCompensacao = true;
    /**
     * Gera o Nosso Número. (Utiliza a mesma forma de gerar o 'Nosso Número' do Bradesco)
     *
     * @return string
     */
    protected function gerarNossoNumero()
    {
        return Util::numberFormatGeral($this->getNumero(), 11)
            . CalculoDV::bradescoNossoNumero($this->getCarteira(), $this->getNumero());
    }

    /**
     * Seta dias para baixa automática
     *
     * @param int $baixaAutomatica
     *
     * @return $this
     * @throws \Exception
     */
    public function setDiasBaixaAutomatica($baixaAutomatica)
    {
        if ($this->getDiasProtesto() > 0) {
            throw new \Exception('Você deve usar dias de protesto ou dias de baixa, nunca os 2');
        }
        $baixaAutomatica = (int) $baixaAutomatica;
        $this->diasBaixaAutomatica = $baixaAutomatica > 0 ? $baixaAutomatica : 0;
        return $this;
    }

    /**
     * Método que retorna o nosso numero usado no boleto. alguns bancos possuem algumas diferenças.
     *
     * @return string
     */
    public function getNossoNumeroBoleto()
    {
        return Util::numberFormatGeral($this->getCarteira(), 2) . ' / ' .  substr_replace($this->getNossoNumero(), '-', -1, 0);
    }
    /**
     * Método para gerar o código da posição de 20 a 44
     *
     * @return string
     */
    protected function getCampoLivre()
    {
        if ($this->campoLivre) {
            return $this->campoLivre;
        }

        $campoLivre = Util::numberFormatGeral($this->getAgencia(), 4);
        $campoLivre .= Util::numberFormatGeral($this->getCarteira(), 2);
        $campoLivre .= Util::numberFormatGeral($this->getNumero(), 11);
        $campoLivre .= Util::numberFormatGeral($this->getConta(), 7);
        $campoLivre .= '0';

        return $this->campoLivre = $campoLivre;
    }

    /**
     * Método onde qualquer boleto deve extender para gerar o código da posição de 20 a 44
     *
     * @param $campoLivre
     *
     * @return array
     */
    public static function parseCampoLivre($campoLivre) {
        return [
            'convenio' => null,
            'agenciaDv' => null,
            'contaCorrenteDv' => null,
            'agencia' => substr($campoLivre, 0, 4),
            'carteira' => substr($campoLivre, 4, 2),
            'nossoNumero' => substr($campoLivre, 6, 11),
            'nossoNumeroDv' => null,
            'nossoNumeroFull' => substr($campoLivre, 6, 11),
            'contaCorrente' => substr($campoLivre, 17, 7),
        ];
    }
}
