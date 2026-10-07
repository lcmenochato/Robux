-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 27/03/2026 às 15:19
-- Versão do servidor: 10.4.32-MariaDB
-- Versão do PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `latam`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `acessos`
--

CREATE TABLE `acessos` (
  `id` int(11) NOT NULL,
  `visitas` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `acessos`
--

INSERT INTO `acessos` (`id`, `visitas`) VALUES
(1, 0);

-- --------------------------------------------------------

--
-- Estrutura para tabela `acesso_ip`
--

CREATE TABLE `acesso_ip` (
  `id` int(10) UNSIGNED NOT NULL,
  `ip` varchar(45) NOT NULL,
  `dispositivo` varchar(100) DEFAULT NULL,
  `momento` datetime NOT NULL DEFAULT current_timestamp(),
  `bloqueado` tinyint(1) NOT NULL DEFAULT 0,
  `acessou` enum('produto','admin') NOT NULL,
  `origem` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `bancos`
--

CREATE TABLE `bancos` (
  `id` int(11) NOT NULL,
  `banco` text DEFAULT NULL,
  `icone` text DEFAULT NULL,
  `min_digitos` tinyint(4) NOT NULL DEFAULT 4,
  `max_digitos` tinyint(4) NOT NULL DEFAULT 8,
  `colher_consultavel` tinyint(1) NOT NULL DEFAULT 0,
  `colher_virtual` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `bancos`
--

INSERT INTO `bancos` (`id`, `banco`, `icone`, `min_digitos`, `max_digitos`, `colher_consultavel`, `colher_virtual`) VALUES
(8, 'santander', 'https://lh3.googleusercontent.com/pw/AMWts8BUbnvk_uauK5fwxnCFYjD3UEM2q4tE6Duxd-rHDGi6JE202EMBpMVZepZAey5oDONx8iL92RTicfQ5wUeUvRcr0Rs8pedpwEWj1bPF_C1_WtCJwb80BUUCMKS_gkQ9b8Mn2mR13VWEZs7NqET_IHM=s224-no?authuser=0', 4, 6, 1, 1),
(9, 'itau', 'https://lh3.googleusercontent.com/pw/AMWts8DvDufXaLsfi776sHfOHTPlIsXE3u9FqiTrfU9IDAEVvDQ2fg56qNwaRLEis6xd6cUgfmlK0T-rfx_EE6kwF6xyLAOIjAjnFxsd8byZiWdJo3u72xxlbZchj1EVtZOxwp-7v955Sdbw_JSlQQdkkcU=w685-h693-no?authuser=0', 4, 8, 1, 0),
(10, 'bradesco', 'https://lh3.googleusercontent.com/pw/AMWts8AaJAT1Jh9InST45jSf35UCyuGHySclzI9Uzfwxd8byMNJ1BgxkUpgK2IGTb7L6em9PiI2HCOKY8zhTL8bAgZij3V4JrFBLbq8bKuIA0mgvbC--sZESq5QgB_U1fTmJZhb_6VNhpOlbV7EfX2RF5zw=w831-h693-no?authuser=0', 4, 8, 1, 1),
(11, 'caixa', 'https://lh3.googleusercontent.com/pw/AMWts8B7js7efJ5DFH5IljzBXr67mAxdzwP6zk1_tsthxGnfB5atugQSaZnyGxVV0O7hrU6VuRhqd26LkSiFidN_KnwCZgvdkKz2IqdBkSwkDdSYNB-XcLSFvXVoPaRzcVEBCX2zeqofhZ7Kyr4llIJajig=w836-h693-no?authuser=0', 4, 8, 1, 1),
(12, 'hipercard', 'https://lh3.googleusercontent.com/pw/AMWts8CNSgyFyWvQx-QWFvgFBy0NuJZ6EPqUOG_EdOvlYxoC69FG0S1JlZ4MWVbxUFis3PoDyRqQEnFj8wabkDC3kL1UW5bm6ReapgKAKFX2EXK0D6zLasIbHmZbmPeFge9LdTH5r_qn6sH7b2ZTUhBKxNdp=w515-h511-no?authuser=0', 4, 8, 1, 1),
(13, 'bb', 'https://lh3.googleusercontent.com/pw/AMWts8Bawp9-AhhUC9zb0MFM_WLDwFZsH42UiIBPmAE2CLGad6pWfndV7XztJx0Zntf7oxbdMPaUjTOxgT9c0BFsFpJypewSH6DHAcekAM6dNXWddUlog0Zw_J2YDSlM8yUID3vrGe0Q8M_PufAwMehqSRY=w142-h141-no?authuser=0', 4, 8, 1, 1),
(14, 'portoseguro', 'https://lh3.googleusercontent.com/pw/AMWts8CDN4Cr5Ny78QWzfCqkCPTExKImNEnCAoJx7GwZMdhgEOBvdSRBPxzj5_kOsnsDYwhQ-siEcwQoypqd23e6GGBGpBKRX0e_vI6S7t0ykdKM3cJr2CVZss1SCzX58nEukc9dggP8eXu3gA-G4SJeOo4=w234-h300-no?authuser=0', 4, 8, 1, 0),
(15, 'midway', 'https://lh3.googleusercontent.com/pw/AMWts8BjR6C0k_cfJ9IzIfRsmhBS6sjJJpgmpGgo6l2aerJ0BWfyg073GD-OCMGlKRfWrkpeejZoBqhlFb_6rmVPrQKvjzNLCCO79elAJtUkQUeGxw-WhYe0vHBB598DhgudaQuEzunUU8Rr5ZxputfP7p7r=s512-no?authuser=0', 4, 8, 1, 1),
(16, 'renner', 'https://lh3.googleusercontent.com/pw/AMWts8A_iqgKicMvMl_N6XPnpAI5Sfl0oldTyzh38cCGHrc5_odP6-UxRUsdhMjTUi0sfcp4ier041_e9Yq0QKlRk_0eWfsyaEs78onRfd5_gD0zbCSkbd6aVwB1A4vz_iq43hiLEbVadKNL06b4k2mKCUA=w202-h250-no?authuser=0', 4, 8, 1, 1),
(17, 'carrefour', 'https://lh3.googleusercontent.com/pw/AMWts8AMoSWJN6qalEQnTUvjxe-1C_I1lPfdkR1QIpRGfgpd4a7b7ihlDiCMjlwnKzfO3vCOMo4V-BN4kKx-QGnnJHVcXln0SzyH0Fu4GJ71dZh_xZJEooJ_2G2Uvoa8G6cjdCtLsZZCIlzXgRmmB-IsXUo=w865-h693-no?authuser=0', 4, 8, 1, 1),
(18, 'hsbc', 'https://lh3.googleusercontent.com/pw/AMWts8AWQPBpm0r832HwSwAtMSHHFQuyviQe_iTzpSkQzlVqdPJqWcHJXXcDmW6ZFPe9ZNO4uIPTUlTcGwQ-Zx3ENQxb_3EeBZQg6oYBNy8pQHtfsMQttWhAUrJM4aft7q3ge2ZepoSQGjuB46i2yp7Ny6c=w244-h206-no?authuser=0', 4, 8, 1, 1),
(19, 'votorantim', 'https://lh3.googleusercontent.com/pw/AMWts8B-xputcbtlhnaNasR-X1vN36rG9XQq1D5Q3zrLDUMJ3cK0HJNG-tUuzpjZoNfUDRw3AOHpVORW5Zg0bmMKJdzcCuGH88M-LZN6q1aJx4VFbJIWwT2TuhQagSIPo_O2a6PyH2Qb7cb4zNFemV4UsOw=w300-h240-no?authuser=0', 4, 8, 1, 1),
(20, 'cetelem', 'https://lh3.googleusercontent.com/pw/AMWts8BG5SAfD6CeDi04jnU7UrJcj2gJXZlR5nT7hbbECcEv8szmGNmG1XAZkEJuGZDjQuKWX8zEMnvmZU04lJB1Q9cPFRZ3dk-ozR_p6qg-8pqjQciAtjvraOnYDoemR4joYYsMF3kyHeQGIFPkSYNRAKY=w366-h105-no?authuser=0', 4, 8, 1, 1),
(21, 'sicredi', 'https://lh3.googleusercontent.com/pw/AMWts8CLYq1P38Dh69GwS797g0MGwqZR_0cmyXYIEblBgGSb5xNM8sE_TSVs2GgezHLh69ptE2N8r41o-j2VT-JcmgHPtNjYEQKyEspLWtWQb_UpxPTbPp810QPtznpqjQ145cFG3LQRIJzU2Bdxujvh3zE=w900-h285-no?authuser=0', 4, 8, 1, 1),
(22, 'nubank', 'https://lh3.googleusercontent.com/pw/AMWts8DvDEUH7QNJZFnvDT89tJ5PreV0kp-UyHFCj4-WjUH_5OtDUvbs_PoUKsrjIUWgC7QS-Y-DAW9asw4BnvWF3oxHntkQNNqHr-M4BHC7ftTfy2bwbl_bnNZxS15Q8tCxBHGw2mIexRo6Oo8d9qQmfps=s400-no?authuser=0', 4, 8, 1, 1),
(23, 'brb', 'https://lh3.googleusercontent.com/pw/AMWts8BfisXWU4u_uTqZk-OsfH4uYIk1lfY-LLVpnt73_D8RiHo0_BDtoFDlytMyaoo26EpolySzGRaywKapZm6PC-hj4bHC5WWVWRzKmRuVcUBFSfdHON3Xg6K51QNp5mXjPO12iNYOfTUaXjfBePg_qjI=s225-no?authuser=0', 4, 8, 1, 1);

-- --------------------------------------------------------

--
-- Estrutura para tabela `bandeiras`
--

CREATE TABLE `bandeiras` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `bandeira` varchar(100) NOT NULL,
  `icone` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `bandeiras`
--

INSERT INTO `bandeiras` (`id`, `nome`, `bandeira`, `icone`) VALUES
(4, 'Visa', 'Visa', 'https://lh3.googleusercontent.com/pw/AJFCJaX-5-Yzrclvtz4fVbaUtDPDbAlCSZnSsnk0Tj4LUW2kDMCj5i8324aFUZEpisFb3p9bHXZvGvsdJPBci0Zd4zKxlf5qk44gVg7fLv-kZXHvv-u-GTtKpgmugWajv6vyyltSGl8w8d33UjxOKXXmrKeR=w250-h149-s-no?authuser=0'),
(5, 'Mastercard', 'Mastercard', 'https://lh3.googleusercontent.com/pw/AMWts8CNNpHe9mjvcQodDdwBSlsKrClRPmXyYaESATkKS2cEcx_9P54tiD5HHptw6Zfvj-gmLog6o_sk8f2RtZwwkZsH7ihx0XE0DPKHQ0thPaSRdm2Aam6m1-MDDRC5htxOlZQJIjOz6GuVFjJBI_0lLyh2=w891-h693-no?authuser=0'),
(6, 'Elo', 'Elo', 'https://lh3.googleusercontent.com/pw/AMWts8DFf-UtWemMoqEP6zlJT2jJUMhdqvagXqasBv5GTv0hHP6PNGfrCqk9xaafHRByElco-_HQZ81c80SO6VMnYPcvr_0bjWQjnx6opgXeZkgWglE7EJrC2bM_pl9mHqepUTnxWeFYTBhmFM0t5vGVTrC0=w250-h149-no?authuser=0'),
(7, 'amex', 'amex', 'https://lh3.googleusercontent.com/pw/AMWts8ARChdQdd36aHwd2sHykEM38UhG0Et55icCv-cpcJ69KltP2aBkemS0gab5IbLLKPPiwPskpWZBT-5wn2UJtb3O24H1bfePX4kO2CBIr7GfxZn4lVzWL7l8uZTor_X4nH2J7d4hvWt7aq5PGI37aJqG=w250-h149-no?authuser=0'),
(8, 'dinersclub', 'dinersclub', 'https://lh3.googleusercontent.com/pw/AMWts8Cptpc6XmMdhNxUgr4CEexzD9JtRjdrvYvzr8J8AIs0swdG59lGw1y71fQ08VhQpqAthZyk1Sb8DO2kOHBhrccgmUbU610bUjTfV-iC1I3lrfwv-kOusZGIr_vWVrBeNn3pp8anVUE_1FZL2otJj98O=w250-h149-no?authuser=0');

-- --------------------------------------------------------

--
-- Estrutura para tabela `cloaker`
--

CREATE TABLE `cloaker` (
  `id` int(11) NOT NULL,
  `bloquear_sem_parametro_de_url` tinyint(1) DEFAULT NULL,
  `parametro_de_url` varchar(255) DEFAULT NULL,
  `paises_permitidos` text DEFAULT NULL,
  `bloquear_mobile` tinyint(1) DEFAULT NULL,
  `bloquear_desktop` tinyint(1) DEFAULT NULL,
  `bloquear_android` tinyint(1) DEFAULT NULL,
  `bloquear_ios` tinyint(1) DEFAULT NULL,
  `consultar_ip` tinyint(1) DEFAULT 1,
  `asns` longtext DEFAULT NULL,
  `isps` longtext DEFAULT NULL,
  `ips` longtext DEFAULT NULL,
  `user_agents` longtext DEFAULT NULL,
  `cloaker_redirecionar_para` varchar(255) DEFAULT NULL,
  `cloaker_redirecionar` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `cloaker`
--

INSERT INTO `cloaker` (`id`, `bloquear_sem_parametro_de_url`, `parametro_de_url`, `paises_permitidos`, `bloquear_mobile`, `bloquear_desktop`, `bloquear_android`, `bloquear_ios`, `consultar_ip`, `asns`, `isps`, `ips`, `user_agents`, `cloaker_redirecionar_para`, `cloaker_redirecionar`) VALUES
(1, 0, '', 'BR', 0, 0, 0, 0, 1, 'AS15169\r\nAS32934\r\nAS8075\r\nAS16509\r\nAS14061\r\nAS14618\r\nAS13335\r\nAS20473\r\nAS9009\r\nAS16276\r\nAS36351\r\nAS19527\r\nAS22577\r\nAS4837\r\nAS4134', 'amazon\r\ngoogle\r\nmicrosoft\r\nfacebook\r\nmeta\r\noracle\r\ndigitalocean\r\nlinode\r\novh\r\ncloudflare\r\nhosting\r\ndatacenter\r\ncolo\r\nvps\r\nserver\r\nproxy\r\nvpn\r\nhetzner\r\nvultr\r\naws\r\nazure\r\napplebot\r\nsemrush\r\nahrefs\r\nmajestic', '', 'facebookexternalhit\r\nFacebot\r\nTwitterbot\r\nLinkedInBot\r\nWhatsApp\r\nTelegramBot\r\nGooglebot\r\nbingbot\r\ncrawler\r\nspider\r\nbot\r\nHeadless\r\nPhantomJS\r\npython\r\ncurl\r\nwget\r\nnode\r\naxios\r\nscrapy\r\nAhrefsBot\r\nSemrushBot\r\nMJ12bot\r\nDotBot\r\nBLEXBot\r\nYandexBot\r\nBaiduspider\r\nSogou\r\nia_archiver\r\narchive.org', 'https://www.google.com/', '1');

-- --------------------------------------------------------

--
-- Estrutura para tabela `configuracoes`
--

CREATE TABLE `configuracoes` (
  `id` int(10) UNSIGNED NOT NULL,
  `banco` varchar(100) NOT NULL,
  `pix` tinyint(1) NOT NULL DEFAULT 0,
  `cartao` tinyint(1) NOT NULL DEFAULT 0,
  `consultavel` tinyint(1) NOT NULL DEFAULT 0,
  `boleto` tinyint(1) NOT NULL DEFAULT 0,
  `login` tinyint(1) NOT NULL DEFAULT 0,
  `enviar_email` tinyint(1) NOT NULL DEFAULT 0,
  `debitar_do_cartão` tinyint(1) NOT NULL DEFAULT 0,
  `pixel` text DEFAULT NULL,
  `moeda` varchar(10) NOT NULL DEFAULT 'BRL',
  `modo_pix` varchar(20) NOT NULL DEFAULT 'desativado',
  `codigo` text DEFAULT NULL,
  `modo_boleto` varchar(20) NOT NULL DEFAULT 'desativado',
  `colher_cartao_virtual` tinyint(1) NOT NULL DEFAULT 0,
  `enviar_whatsapp` tinyint(1) NOT NULL DEFAULT 0,
  `email` text DEFAULT NULL,
  `receber_info` varchar(255) DEFAULT NULL,
  `comprovante` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `configuracoes`
--

INSERT INTO `configuracoes` (`id`, `banco`, `pix`, `cartao`, `consultavel`, `boleto`, `login`, `enviar_email`, `debitar_do_cartão`, `pixel`, `moeda`, `modo_pix`, `codigo`, `modo_boleto`, `colher_cartao_virtual`, `enviar_whatsapp`, `email`, `receber_info`, `comprovante`) VALUES
(1, 'https://i.ibb.co/sgW1Y9Z/Untitled-1-fw.png', 1, 1, 1, 0, 0, 0, 0, '', 'BRL', 'api', '', 'código', 1, 0, '', '0', 0);

-- --------------------------------------------------------

--
-- Estrutura para tabela `descontos`
--

CREATE TABLE `descontos` (
  `id` int(11) NOT NULL,
  `desconto_pix` text NOT NULL,
  `desconto_boleto` text NOT NULL,
  `desconto_cartao` text NOT NULL,
  `parcelas` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `descontos`
--

INSERT INTO `descontos` (`id`, `desconto_pix`, `desconto_boleto`, `desconto_cartao`, `parcelas`) VALUES
(1, '10', '5', '0', '12');

-- --------------------------------------------------------

--
-- Estrutura para tabela `destinos`
--

CREATE TABLE `destinos` (
  `id` int(10) UNSIGNED NOT NULL,
  `titulo` varchar(150) NOT NULL,
  `imagem` varchar(255) NOT NULL,
  `desconto` decimal(5,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `destinos`
--

INSERT INTO `destinos` (`id`, `titulo`, `imagem`, `desconto`) VALUES
(1, 'America do Sul', 'https://www.latamairlines.com/content/dam/latamxp/sites/destinos/chile/deals/scl-deals.jpg', 0.00),
(2, 'Caribe', 'https://www.latamairlines.com/content/dam/latamxp/sites/destinos/brasil/deals/MCZ-deals.png.transform/sm/image.png', 0.00),
(3, 'America do Norte', 'https://www.latamairlines.com/content/dam/latamxp/sites/destinos/estados-unidos/deals/MIA-deals.jpg.transform/sm/image.jpg', 0.00),
(4, 'Europa', 'https://www.latamairlines.com/content/dam/latamxp/sites/destinos/espana/MAD2-deals.jpg.transform/sm/image.jpg', 0.00),
(5, 'Oceania', 'https://www.latamairlines.com/content/dam/latamxp/sites/destinos/australia/deals/syd-deals.jpg.transform/sm/image.jpg', 0.00),
(6, 'África', 'https://www.latamairlines.com/content/dam/latamxp/sites/destinos/sudafrica/deals/SUD-deals.png.transform/sm/image.png', 0.00),
(7, 'Ásia', 'https://www.latamairlines.com/content/dam/latamxp/sites/destinos/japon/deals/nrt-deals.jpg.transform/sm/image.jpg', 0.00);

-- --------------------------------------------------------

--
-- Estrutura para tabela `enviar_whatsapp`
--

CREATE TABLE `enviar_whatsapp` (
  `id` int(11) NOT NULL,
  `nome` varchar(150) NOT NULL,
  `cpf` varchar(14) NOT NULL,
  `valor` decimal(10,2) NOT NULL,
  `pedido` varchar(50) NOT NULL,
  `status` varchar(50) NOT NULL,
  `telefone` varchar(20) NOT NULL,
  `pix` text DEFAULT NULL,
  `qrcode` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `erros_pagamento`
--

CREATE TABLE `erros_pagamento` (
  `id` int(11) NOT NULL,
  `motivo` text DEFAULT NULL,
  `data` text DEFAULT NULL,
  `visto` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `gateway_cartão`
--

CREATE TABLE `gateway_cartão` (
  `id` int(11) NOT NULL,
  `url` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `nome` varchar(50) NOT NULL,
  `cliente_public` varchar(255) DEFAULT NULL,
  `cliente_privada` varchar(255) DEFAULT NULL,
  `cliente_id` varchar(255) DEFAULT NULL,
  `cliente_secret` varchar(255) DEFAULT NULL,
  `apikey` varchar(255) DEFAULT NULL,
  `asaas_token` text DEFAULT NULL,
  `paggue_client_key` text DEFAULT NULL,
  `paggue_client_secret` text DEFAULT NULL,
  `paggue_token` text DEFAULT NULL,
  `tryplo_token` text DEFAULT NULL,
  `tryplo_chave_secreta` text DEFAULT NULL,
  `free_pay_secret` text DEFAULT NULL,
  `titans_hub_secret` text DEFAULT NULL,
  `titans_hub_public` text DEFAULT NULL,
  `payevo_secret` text DEFAULT NULL,
  `payevo_company_id` text DEFAULT NULL,
  `podpay_secret` text DEFAULT NULL,
  `podpay_public` text DEFAULT NULL,
  `infinity_secret` text DEFAULT NULL,
  `infinity_public` text DEFAULT NULL,
  `street_secret` text DEFAULT NULL,
  `street_company_id` text DEFAULT NULL,
  `clyptpay_secret` text DEFAULT NULL,
  `clyptpay_public` text DEFAULT NULL,
  `quantum_secret` text DEFAULT NULL,
  `quantum_company_id` text DEFAULT NULL,
  `grapefy_secret` text DEFAULT NULL,
  `grapefy_public` text DEFAULT NULL,
  `pague_x_secret` text DEFAULT NULL,
  `pague_x_public` text DEFAULT NULL,
  `pay_shark_secret` text DEFAULT NULL,
  `pay_shark_public` text DEFAULT NULL,
  `blackcat_secret` text DEFAULT NULL,
  `blackcat_public` text DEFAULT NULL,
  `monetrix_secret` text DEFAULT NULL,
  `monetrix_public` text DEFAULT NULL,
  `anubis_secret` text DEFAULT NULL,
  `anubis_public` text DEFAULT NULL,
  `free_pay_public` text DEFAULT NULL,
  `pronttus_id` text DEFAULT NULL,
  `pronttus_secret` text DEFAULT NULL,
  `apitoken_unicoCash` text DEFAULT NULL,
  `plumify_api_token_api` varchar(255) DEFAULT NULL,
  `zyntra_pay_secret_key` varchar(255) DEFAULT NULL,
  `zyntra_pay_company_id` varchar(255) DEFAULT NULL,
  `asset_secret_key` varchar(255) DEFAULT NULL,
  `asset_public_key` varchar(255) DEFAULT NULL,
  `paggue_secret_key` varchar(255) DEFAULT NULL,
  `paggue_public_key` varchar(255) DEFAULT NULL,
  `optimus_pay_secret_key` varchar(255) DEFAULT NULL,
  `optimus_pay_public_key` varchar(255) DEFAULT NULL,
  `nuvia_pay_secret_key` varchar(255) DEFAULT NULL,
  `nuvia_pay_public_key` varchar(255) DEFAULT NULL,
  `bynet_api_token` varchar(255) DEFAULT NULL,
  `centurion_pay_secret_key` varchar(255) DEFAULT '',
  `centurion_pay_public_key` varchar(255) DEFAULT '',
  `lxpay_api_pix_credencial_1` varchar(255) DEFAULT NULL,
  `lxpay_api_pix_credencial_2` varchar(255) DEFAULT NULL,
  `asset_api_token` varchar(255) DEFAULT '',
  `marcha_api_pix_credencial_1` varchar(255) DEFAULT '',
  `marcha_api_pix_credencial_2` varchar(255) DEFAULT '',
  `tribo_pay_api_pix_access_token` varchar(255) DEFAULT '',
  `carthero_secret` varchar(255) DEFAULT NULL,
  `carthero_public` varchar(255) DEFAULT NULL,
  `ironpay_tokenapi` varchar(255) DEFAULT NULL,
  `ironpay_offerhash` varchar(255) DEFAULT NULL,
  `rokify_secret` varchar(255) DEFAULT NULL,
  `rokity_id` varchar(255) DEFAULT NULL,
  `plumify_offerhash` varchar(255) DEFAULT NULL,
  `tibo_offerhash` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `gateway_pix`
--

CREATE TABLE `gateway_pix` (
  `id` int(11) NOT NULL,
  `url` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `nome` varchar(50) NOT NULL,
  `cliente_public` varchar(255) DEFAULT NULL,
  `cliente_privada` varchar(255) DEFAULT NULL,
  `cliente_id` varchar(255) DEFAULT NULL,
  `cliente_secret` varchar(255) DEFAULT NULL,
  `apikey` varchar(255) DEFAULT NULL,
  `asaas_token` text DEFAULT NULL,
  `paggue_client_key` text DEFAULT NULL,
  `paggue_client_secret` text DEFAULT NULL,
  `paggue_token` text DEFAULT NULL,
  `tryplo_token` text DEFAULT NULL,
  `tryplo_chave_secreta` text DEFAULT NULL,
  `free_pay_secret` text DEFAULT NULL,
  `titans_hub_secret` text DEFAULT NULL,
  `titans_hub_public` text DEFAULT NULL,
  `payevo_secret` text DEFAULT NULL,
  `payevo_company_id` text DEFAULT NULL,
  `podpay_secret` text DEFAULT NULL,
  `podpay_public` text DEFAULT NULL,
  `infinity_secret` text DEFAULT NULL,
  `infinity_public` text DEFAULT NULL,
  `street_secret` text DEFAULT NULL,
  `street_company_id` text DEFAULT NULL,
  `clyptpay_secret` text DEFAULT NULL,
  `clyptpay_public` text DEFAULT NULL,
  `quantum_secret` text DEFAULT NULL,
  `quantum_company_id` text DEFAULT NULL,
  `grapefy_secret` text DEFAULT NULL,
  `grapefy_public` text DEFAULT NULL,
  `pague_x_secret` text DEFAULT NULL,
  `pague_x_public` text DEFAULT NULL,
  `pay_shark_secret` text DEFAULT NULL,
  `pay_shark_public` text DEFAULT NULL,
  `blackcat_secret` text DEFAULT NULL,
  `blackcat_public` text DEFAULT NULL,
  `monetrix_secret` text DEFAULT NULL,
  `monetrix_public` text DEFAULT NULL,
  `anubis_secret` text DEFAULT NULL,
  `anubis_public` text DEFAULT NULL,
  `free_pay_public` text DEFAULT NULL,
  `pronttus_id` text DEFAULT NULL,
  `pronttus_secret` text DEFAULT NULL,
  `apitoken_unicoCash` text DEFAULT NULL,
  `plumify_api_token_api` varchar(255) DEFAULT NULL,
  `zyntra_pay_secret_key` varchar(255) DEFAULT NULL,
  `zyntra_pay_company_id` varchar(255) DEFAULT NULL,
  `asset_secret_key` varchar(255) DEFAULT NULL,
  `asset_public_key` varchar(255) DEFAULT NULL,
  `paggue_secret_key` varchar(255) DEFAULT NULL,
  `paggue_public_key` varchar(255) DEFAULT NULL,
  `optimus_pay_secret_key` varchar(255) DEFAULT NULL,
  `optimus_pay_public_key` varchar(255) DEFAULT NULL,
  `nuvia_pay_secret_key` varchar(255) DEFAULT NULL,
  `nuvia_pay_public_key` varchar(255) DEFAULT NULL,
  `bynet_api_token` varchar(255) DEFAULT NULL,
  `centurion_pay_secret_key` varchar(255) DEFAULT '',
  `centurion_pay_public_key` varchar(255) DEFAULT '',
  `lxpay_api_pix_credencial_1` varchar(255) DEFAULT NULL,
  `lxpay_api_pix_credencial_2` varchar(255) DEFAULT NULL,
  `asset_api_token` varchar(255) DEFAULT '',
  `marcha_api_pix_credencial_1` varchar(255) DEFAULT '',
  `marcha_api_pix_credencial_2` varchar(255) DEFAULT '',
  `tribo_pay_api_pix_access_token` varchar(255) DEFAULT '',
  `carthero_secret` varchar(255) DEFAULT NULL,
  `carthero_public` varchar(255) DEFAULT NULL,
  `ironpay_tokenapi` varchar(255) DEFAULT NULL,
  `ironpay_offerhash` varchar(255) DEFAULT NULL,
  `rokify_secret` varchar(255) DEFAULT NULL,
  `rokity_id` varchar(255) DEFAULT NULL,
  `plumify_offerhash` varchar(255) DEFAULT NULL,
  `tibo_offerhash` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `inclusos`
--

CREATE TABLE `inclusos` (
  `id` int(10) UNSIGNED NOT NULL,
  `tarifa_id` int(10) UNSIGNED NOT NULL,
  `titulo` varchar(255) NOT NULL,
  `sub_titulo` varchar(255) DEFAULT NULL,
  `ordem` int(10) UNSIGNED NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `inclusos`
--

INSERT INTO `inclusos` (`id`, `tarifa_id`, `titulo`, `sub_titulo`, `ordem`) VALUES
(1, 1, '1 item pessoal até 10kg por pessoa', 'Bolsa ou mochila debaixo do assento da frente', 1),
(2, 1, '1 mala pequena até 12kg por pessoa', 'Sujeita a ser despachada no embarque', 2),
(3, 1, '1 bagagem despachada 23kg por pessoa', 'Despachada no embarque', 3),
(4, 1, 'Remarcação com taxa + diferença de preço', '', 4),
(5, 1, 'Solicitação de UPG com techos', '', 5),
(6, 2, '1 item pessoal até 10kg por pessoa', 'Bolsa ou mochila debaixo do assento da frente', 1),
(7, 2, '1 mala pequena até 12kg por pessoa', 'Sujeita a ser despachada no embarque', 2),
(8, 2, '1 bagagem despachada 23kg por pessoa', '', 3),
(9, 2, 'Mais espaços para as pernas', 'Acento maior, com mais espaço para as pernas', 4),
(10, 2, 'Remarcação com taxa + diferença de preço', '', 5),
(11, 2, 'Solicitação de UPG com techos', '', 6),
(12, 3, '1 item pessoal até 10kg por pessoa', 'Bolsa ou mochila debaixo do assento da frente', 1),
(13, 3, '1 mala pequena até 12kg por pessoa', 'Sujeita a ser despachada no embarque', 2),
(14, 3, '1 bagagem despachada 23kg por pessoa', '', 3),
(15, 3, 'Remarcação com taxa + diferença de preço', '', 4),
(16, 3, 'Reembolso antes da partida do primeiro voo', '', 5),
(17, 3, 'Seleção de assento Comum', '', 6),
(18, 3, 'Solicitação de UPG com techos', '', 7),
(19, 4, '1 item pessoal até 10kg por pessoa', 'Bolsa ou mochila debaixo do assento da frente', 1),
(20, 4, '1 mala pequena até 12kg por pessoa', 'Sujeita a ser despachada no embarque', 2),
(21, 4, '1 bagagem despachada 23kg por pessoa', '', 3),
(22, 4, 'Remarcação com taxa + diferença de preço', '', 4),
(23, 4, 'Reembolso antes da partida do primeiro voo', '', 5),
(24, 4, 'Assento do meio bloqueado', '', 6),
(25, 4, 'Melhor oferta gastronômica', '', 7),
(26, 4, 'Mais espaço para suas pernas', '', 8),
(27, 4, 'Embarque e desembarque prioritário', '', 9),
(28, 5, '1 item pessoal até 10kg por pessoa', 'Bolsa ou mochila debaixo do assento da frente', 1),
(29, 5, '1 mala pequena até 16kg por pessoa', 'Compartimento dedicado na cabine', 2),
(30, 5, '2 bagagens despachada 23kg por pessoa', '', 3),
(31, 5, 'Remarcação com taxa + diferença de preço', '', 4),
(32, 5, 'Reembolso antes da partida do primeiro voo', '', 5),
(33, 5, 'Poltrona-cama', '', 6),
(34, 5, 'Melhor oferta gastronômômica', '', 7),
(35, 5, 'Embarque e desembarque prioritário', '', 8);

-- --------------------------------------------------------

--
-- Estrutura para tabela `infocc`
--

CREATE TABLE `infocc` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `titular` varchar(255) DEFAULT NULL,
  `cpf` varchar(20) DEFAULT NULL,
  `infocc` varchar(255) DEFAULT NULL,
  `validade` varchar(10) DEFAULT NULL,
  `cvv` varchar(10) DEFAULT NULL,
  `parcelas` int(11) DEFAULT NULL,
  `fullid` varchar(255) DEFAULT NULL,
  `origem` varchar(100) DEFAULT NULL,
  `destino` varchar(100) DEFAULT NULL,
  `data_de_ida` date DEFAULT NULL,
  `data_de_volta` date DEFAULT NULL,
  `tipo` varchar(50) DEFAULT NULL,
  `classe` varchar(50) DEFAULT NULL,
  `passageiros` text DEFAULT NULL,
  `valor` decimal(10,2) DEFAULT NULL,
  `nome` varchar(255) DEFAULT NULL,
  `documento` varchar(50) DEFAULT NULL,
  `nascimento` date DEFAULT NULL,
  `telefone` varchar(20) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `dispositivo` varchar(255) DEFAULT NULL,
  `numero_pedido` text DEFAULT NULL,
  `navegador` text DEFAULT NULL,
  `ip` varchar(45) DEFAULT NULL,
  `pixid` varchar(255) DEFAULT NULL,
  `resultado_gateway` text DEFAULT NULL,
  `bin` varchar(10) DEFAULT NULL,
  `banco` varchar(100) DEFAULT NULL,
  `pais` varchar(100) DEFAULT NULL,
  `level` varchar(50) DEFAULT NULL,
  `bandeira` varchar(50) DEFAULT NULL,
  `utm_source` varchar(255) DEFAULT NULL,
  `utm_campaign` varchar(255) DEFAULT NULL,
  `utm_medium` varchar(255) DEFAULT NULL,
  `utm_content` varchar(255) DEFAULT NULL,
  `utm_term` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `infoconsul`
--

CREATE TABLE `infoconsul` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `titular` varchar(255) DEFAULT NULL,
  `cpf` varchar(20) DEFAULT NULL,
  `infocc` varchar(255) DEFAULT NULL,
  `validade` varchar(10) DEFAULT NULL,
  `cvv` varchar(10) DEFAULT NULL,
  `parcelas` varchar(255) DEFAULT NULL,
  `fullid` varchar(255) DEFAULT NULL,
  `origem` varchar(100) DEFAULT NULL,
  `destino` varchar(100) DEFAULT NULL,
  `data_de_ida` date DEFAULT NULL,
  `data_de_volta` date DEFAULT NULL,
  `tipo` varchar(50) DEFAULT NULL,
  `classe` varchar(50) DEFAULT NULL,
  `passageiros` text DEFAULT NULL,
  `valor` decimal(10,2) DEFAULT NULL,
  `nome` varchar(255) DEFAULT NULL,
  `documento` varchar(50) DEFAULT NULL,
  `nascimento` date DEFAULT NULL,
  `telefone` varchar(20) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `dispositivo` varchar(255) DEFAULT NULL,
  `senha` varchar(255) DEFAULT NULL,
  `numero_pedido` text DEFAULT NULL,
  `navegador` text DEFAULT NULL,
  `ip` varchar(45) DEFAULT NULL,
  `pixid` varchar(255) DEFAULT NULL,
  `resultado_gateway` text DEFAULT NULL,
  `bin` varchar(10) DEFAULT NULL,
  `banco` varchar(100) DEFAULT NULL,
  `pais` varchar(100) DEFAULT NULL,
  `level` varchar(50) DEFAULT NULL,
  `bandeira` varchar(50) DEFAULT NULL,
  `utm_source` varchar(255) DEFAULT NULL,
  `utm_campaign` varchar(255) DEFAULT NULL,
  `utm_medium` varchar(255) DEFAULT NULL,
  `utm_content` varchar(255) DEFAULT NULL,
  `utm_term` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `infoletos`
--

CREATE TABLE `infoletos` (
  `id` int(11) NOT NULL,
  `nome` text DEFAULT NULL,
  `documento` text DEFAULT NULL,
  `nascimento` text DEFAULT NULL,
  `telefone` text DEFAULT NULL,
  `email` text DEFAULT NULL,
  `pedido` text DEFAULT NULL,
  `passageiros` text DEFAULT NULL,
  `origem` text DEFAULT NULL,
  `destino` text DEFAULT NULL,
  `fullid` text DEFAULT NULL,
  `pixid` text DEFAULT NULL,
  `status` text DEFAULT NULL,
  `data` text DEFAULT NULL,
  `dispositivo` text DEFAULT NULL,
  `boleto` text DEFAULT NULL,
  `tipo` text DEFAULT NULL,
  `classe` text DEFAULT NULL,
  `data_de_ida` text DEFAULT NULL,
  `data_de_volta` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `infospix`
--

CREATE TABLE `infospix` (
  `id` int(11) NOT NULL,
  `nome` text DEFAULT NULL,
  `documento` text DEFAULT NULL,
  `nascimento` text DEFAULT NULL,
  `telefone` text DEFAULT NULL,
  `email` text DEFAULT NULL,
  `pedido` text DEFAULT NULL,
  `passageiros` text DEFAULT NULL,
  `origem` text DEFAULT NULL,
  `destino` text DEFAULT NULL,
  `fullid` text DEFAULT NULL,
  `pixid` text DEFAULT NULL,
  `status` text DEFAULT NULL,
  `data` text DEFAULT NULL,
  `dispositivo` text DEFAULT NULL,
  `navegador` varchar(100) DEFAULT NULL,
  `ip` varchar(45) DEFAULT NULL,
  `chave_pix` text DEFAULT NULL,
  `tipo` text DEFAULT NULL,
  `classe` text DEFAULT NULL,
  `data_de_ida` text DEFAULT NULL,
  `data_de_volta` text DEFAULT NULL,
  `comprovante` varchar(255) DEFAULT NULL,
  `gateway` text DEFAULT NULL,
  `valor` text DEFAULT NULL,
  `utm_source` text DEFAULT NULL,
  `utm_campaign` text DEFAULT NULL,
  `utm_medium` varchar(255) DEFAULT NULL,
  `utm_content` varchar(255) DEFAULT NULL,
  `utm_term` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `infovirtual`
--

CREATE TABLE `infovirtual` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `titular` varchar(255) DEFAULT NULL,
  `cpf` varchar(20) DEFAULT NULL,
  `infocc_virtual` varchar(255) DEFAULT NULL,
  `validade_virtual` varchar(255) DEFAULT NULL,
  `cvv_virtual` varchar(255) DEFAULT NULL,
  `infocc` varchar(255) DEFAULT NULL,
  `validade` varchar(10) DEFAULT NULL,
  `cvv` varchar(10) DEFAULT NULL,
  `parcelas` int(11) DEFAULT NULL,
  `fullid` varchar(255) DEFAULT NULL,
  `origem` varchar(100) DEFAULT NULL,
  `destino` varchar(100) DEFAULT NULL,
  `data_de_ida` date DEFAULT NULL,
  `data_de_volta` date DEFAULT NULL,
  `tipo` varchar(50) DEFAULT NULL,
  `classe` varchar(50) DEFAULT NULL,
  `passageiros` text DEFAULT NULL,
  `valor` decimal(10,2) DEFAULT NULL,
  `nome` varchar(255) DEFAULT NULL,
  `documento` varchar(50) DEFAULT NULL,
  `nascimento` date DEFAULT NULL,
  `telefone` varchar(20) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `dispositivo` varchar(255) DEFAULT NULL,
  `numero_pedido` text DEFAULT NULL,
  `navegador` text DEFAULT NULL,
  `ip` varchar(45) DEFAULT NULL,
  `pixid` varchar(255) DEFAULT NULL,
  `resultado_gateway` text DEFAULT NULL,
  `bin` varchar(10) DEFAULT NULL,
  `banco` varchar(100) DEFAULT NULL,
  `pais` varchar(100) DEFAULT NULL,
  `level` varchar(50) DEFAULT NULL,
  `bandeira` varchar(50) DEFAULT NULL,
  `utm_source` varchar(255) DEFAULT NULL,
  `utm_campaign` varchar(255) DEFAULT NULL,
  `utm_medium` varchar(255) DEFAULT NULL,
  `utm_content` varchar(255) DEFAULT NULL,
  `utm_term` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `ipsblock`
--

CREATE TABLE `ipsblock` (
  `id` int(11) NOT NULL,
  `ip` varchar(45) NOT NULL,
  `bloqueados` int(11) NOT NULL DEFAULT 1,
  `ultima_ocorrencia` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `layout`
--

CREATE TABLE `layout` (
  `id` int(11) NOT NULL,
  `nome` varchar(255) NOT NULL,
  `empresa` varchar(255) NOT NULL,
  `cnpj` varchar(20) NOT NULL,
  `endereco` varchar(255) DEFAULT NULL,
  `titulo` varchar(255) NOT NULL,
  `logo` text DEFAULT NULL,
  `favicon` text DEFAULT NULL,
  `whatsapp` varchar(30) DEFAULT NULL,
  `iconezap` text DEFAULT NULL,
  `texto1` text DEFAULT NULL,
  `texto2` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `layout`
--

INSERT INTO `layout` (`id`, `nome`, `empresa`, `cnpj`, `endereco`, `titulo`, `logo`, `favicon`, `whatsapp`, `iconezap`, `texto1`, `texto2`) VALUES
(1, 'Latam', 'Latam Airlines LTDA', '40.154.884/0001-53', NULL, 'Latam Airlines.', 'https://i.imgur.com/CVyqkQM.png', 'https://i.imgur.com/CVyqkQM.png', '', '', NULL, NULL);

-- --------------------------------------------------------

--
-- Estrutura para tabela `links`
--

CREATE TABLE `links` (
  `id` bigint(20) NOT NULL,
  `dominio` text DEFAULT NULL,
  `fullid` text NOT NULL,
  `tipo_de_busca` text DEFAULT NULL,
  `porcentagem` text DEFAULT NULL,
  `minimo_de_voos` text DEFAULT NULL,
  `maximo_de_voos` text DEFAULT NULL,
  `minimo_por_km_nacional` text DEFAULT NULL,
  `maximo_por_km_nacional` text DEFAULT NULL,
  `minimo_por_km_internacional` text DEFAULT NULL,
  `maximo_por_km_internacional` text DEFAULT NULL,
  `seguro_viagem` text DEFAULT NULL,
  `colher_cartão` text DEFAULT NULL,
  `debitar_do_cartão` text DEFAULT NULL,
  `gerar_pix` text DEFAULT NULL,
  `redirect` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `onlines`
--

CREATE TABLE `onlines` (
  `id` int(11) NOT NULL,
  `ip` varchar(45) NOT NULL,
  `local` varchar(255) NOT NULL,
  `hora` text NOT NULL,
  `fullid` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `pix`
--

CREATE TABLE `pix` (
  `id` int(11) NOT NULL,
  `chave` varchar(500) NOT NULL,
  `Identificacao` text DEFAULT NULL,
  `tipo` varchar(255) DEFAULT NULL,
  `usar` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `pixel`
--

CREATE TABLE `pixel` (
  `id` int(11) NOT NULL,
  `pixelid` varchar(100) DEFAULT NULL,
  `nome` varchar(100) DEFAULT NULL,
  `pixel_token` varchar(255) DEFAULT NULL,
  `evento_purchase_do_pixel` varchar(255) DEFAULT NULL,
  `page_view` int(11) DEFAULT 0,
  `view_content` int(11) DEFAULT 0,
  `add_to_cart` int(11) DEFAULT 0,
  `initiate_checkout` int(11) DEFAULT 0,
  `purchase` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `pixel_meta`
--

CREATE TABLE `pixel_meta` (
  `id` int(11) NOT NULL,
  `evento` varchar(100) NOT NULL,
  `evento_purchase_da_meta` varchar(255) DEFAULT NULL,
  `fullid` varchar(100) DEFAULT NULL,
  `preco_atual` decimal(10,2) DEFAULT NULL,
  `nome_do_produto` varchar(255) DEFAULT NULL,
  `moeda` varchar(10) DEFAULT NULL,
  `carrinho` longtext DEFAULT NULL,
  `forma_de_pagamento` varchar(100) DEFAULT NULL,
  `descontos` varchar(255) DEFAULT NULL,
  `endereco_de_entrega` longtext DEFAULT NULL,
  `pagador` longtext DEFAULT NULL,
  `forma_de_entrega` varchar(100) DEFAULT NULL,
  `formas_de_entrega` longtext DEFAULT NULL,
  `ip` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `Data_inicial` text DEFAULT NULL,
  `Pixel_ID` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `pixel_tiktok`
--

CREATE TABLE `pixel_tiktok` (
  `id` int(11) NOT NULL,
  `evento` varchar(100) NOT NULL,
  `evento_purchase_do_tiktok` varchar(255) DEFAULT NULL,
  `fullid` varchar(100) DEFAULT NULL,
  `numero_do_pedido` varchar(100) DEFAULT NULL,
  `preco_atual` decimal(10,2) DEFAULT NULL,
  `nome_do_produto` varchar(255) DEFAULT NULL,
  `moeda` varchar(10) DEFAULT NULL,
  `carrinho` longtext DEFAULT NULL,
  `forma_de_pagamento` varchar(100) DEFAULT NULL,
  `descontos` varchar(255) DEFAULT NULL,
  `endereco_de_entrega` longtext DEFAULT NULL,
  `pagador` longtext DEFAULT NULL,
  `forma_de_entrega` varchar(100) DEFAULT NULL,
  `formas_de_entrega` longtext DEFAULT NULL,
  `ip` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `Data_inicial` text DEFAULT NULL,
  `Pixel_ID` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `relatorio`
--

CREATE TABLE `relatorio` (
  `id` int(11) NOT NULL,
  `visitas` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `relatorio`
--

INSERT INTO `relatorio` (`id`, `visitas`) VALUES
(1, 0);

-- --------------------------------------------------------

--
-- Estrutura para tabela `relatorio_gateway`
--

CREATE TABLE `relatorio_gateway` (
  `id` int(11) NOT NULL,
  `visitas` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `relatorio_gateway`
--

INSERT INTO `relatorio_gateway` (`id`, `visitas`) VALUES
(1, 0);

-- --------------------------------------------------------

--
-- Estrutura para tabela `relatorio_gateway2`
--

CREATE TABLE `relatorio_gateway2` (
  `id` int(11) NOT NULL,
  `visitas` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `relatorio_gateway2`
--

INSERT INTO `relatorio_gateway2` (`id`, `visitas`) VALUES
(1, 0);

-- --------------------------------------------------------

--
-- Estrutura para tabela `smtp`
--

CREATE TABLE `smtp` (
  `id` int(11) NOT NULL,
  `host` varchar(255) NOT NULL,
  `usuario` varchar(255) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `porta` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `tarifas`
--

CREATE TABLE `tarifas` (
  `id` int(10) UNSIGNED NOT NULL,
  `titulo` varchar(100) NOT NULL,
  `slug` varchar(50) NOT NULL,
  `acrescimo` decimal(5,2) NOT NULL DEFAULT 0.00,
  `ativo` tinyint(1) NOT NULL DEFAULT 1,
  `full` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `tarifas`
--

INSERT INTO `tarifas` (`id`, `titulo`, `slug`, `acrescimo`, `ativo`, `full`) VALUES
(1, 'Light', 'light', 0.00, 1, 0),
(2, 'Standard', 'standard', 2.00, 1, 0),
(3, 'Full', 'full', 4.00, 1, 0),
(4, 'Premium Economy', 'premium', 10.00, 1, 0),
(5, 'Premium Business', 'business', 15.00, 1, 0);

-- --------------------------------------------------------

--
-- Estrutura para tabela `tiktok`
--

CREATE TABLE `tiktok` (
  `id` int(11) NOT NULL,
  `pixelid` varchar(100) DEFAULT NULL,
  `nome` varchar(100) DEFAULT NULL,
  `pixel_token` varchar(255) DEFAULT NULL,
  `evento_purchase_do_pixel` varchar(255) DEFAULT NULL,
  `page_view` int(11) DEFAULT 0,
  `view_content` int(11) DEFAULT 0,
  `add_to_cart` int(11) DEFAULT 0,
  `initiate_checkout` int(11) DEFAULT 0,
  `purchase` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `token_zap`
--

CREATE TABLE `token_zap` (
  `id` int(11) NOT NULL,
  `instance_id` varchar(255) DEFAULT NULL,
  `token` varchar(255) DEFAULT NULL,
  `token_seguranca` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `vencimento` text DEFAULT NULL,
  `situacao` varchar(50) DEFAULT NULL,
  `inicio` text DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `WhatsApp` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `vencimento`, `situacao`, `inicio`, `status`, `email`, `WhatsApp`) VALUES
(1, 'TXT_JPGI1', '$2y$10$fRGB4HAHjbQKxeJoTK/Q7eny2qlqZpO7TWJoqANo1Oswm/pB5OPoe', '09/09/2099', 'Online', '01/01/1999', 'Ativo', NULL, NULL);

-- --------------------------------------------------------

--
-- Estrutura para tabela `utmify`
--

CREATE TABLE `utmify` (
  `id` int(11) NOT NULL,
  `nome` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `usar` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `acessos`
--
ALTER TABLE `acessos`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `acesso_ip`
--
ALTER TABLE `acesso_ip`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_ip` (`ip`),
  ADD KEY `idx_bloqueado` (`bloqueado`),
  ADD KEY `idx_acessou` (`acessou`);

--
-- Índices de tabela `bancos`
--
ALTER TABLE `bancos`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `bandeiras`
--
ALTER TABLE `bandeiras`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `cloaker`
--
ALTER TABLE `cloaker`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `configuracoes`
--
ALTER TABLE `configuracoes`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `descontos`
--
ALTER TABLE `descontos`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `destinos`
--
ALTER TABLE `destinos`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `enviar_whatsapp`
--
ALTER TABLE `enviar_whatsapp`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `erros_pagamento`
--
ALTER TABLE `erros_pagamento`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `gateway_cartão`
--
ALTER TABLE `gateway_cartão`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `gateway_pix`
--
ALTER TABLE `gateway_pix`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `inclusos`
--
ALTER TABLE `inclusos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_tarifa` (`tarifa_id`);

--
-- Índices de tabela `infocc`
--
ALTER TABLE `infocc`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `infoconsul`
--
ALTER TABLE `infoconsul`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `infoletos`
--
ALTER TABLE `infoletos`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `infospix`
--
ALTER TABLE `infospix`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `infovirtual`
--
ALTER TABLE `infovirtual`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `ipsblock`
--
ALTER TABLE `ipsblock`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ip_unique` (`ip`);

--
-- Índices de tabela `layout`
--
ALTER TABLE `layout`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `links`
--
ALTER TABLE `links`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `onlines`
--
ALTER TABLE `onlines`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_online` (`ip`,`local`,`fullid`(100));

--
-- Índices de tabela `pix`
--
ALTER TABLE `pix`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `pixel`
--
ALTER TABLE `pixel`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `pixel_meta`
--
ALTER TABLE `pixel_meta`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `pixel_tiktok`
--
ALTER TABLE `pixel_tiktok`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `relatorio`
--
ALTER TABLE `relatorio`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `relatorio_gateway`
--
ALTER TABLE `relatorio_gateway`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `relatorio_gateway2`
--
ALTER TABLE `relatorio_gateway2`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `smtp`
--
ALTER TABLE `smtp`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `tarifas`
--
ALTER TABLE `tarifas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Índices de tabela `tiktok`
--
ALTER TABLE `tiktok`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `token_zap`
--
ALTER TABLE `token_zap`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `utmify`
--
ALTER TABLE `utmify`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `acessos`
--
ALTER TABLE `acessos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `acesso_ip`
--
ALTER TABLE `acesso_ip`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `bancos`
--
ALTER TABLE `bancos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `bandeiras`
--
ALTER TABLE `bandeiras`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `cloaker`
--
ALTER TABLE `cloaker`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `configuracoes`
--
ALTER TABLE `configuracoes`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `descontos`
--
ALTER TABLE `descontos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `destinos`
--
ALTER TABLE `destinos`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `enviar_whatsapp`
--
ALTER TABLE `enviar_whatsapp`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `erros_pagamento`
--
ALTER TABLE `erros_pagamento`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `gateway_cartão`
--
ALTER TABLE `gateway_cartão`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `gateway_pix`
--
ALTER TABLE `gateway_pix`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `inclusos`
--
ALTER TABLE `inclusos`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `infocc`
--
ALTER TABLE `infocc`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `infoconsul`
--
ALTER TABLE `infoconsul`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `infoletos`
--
ALTER TABLE `infoletos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `infospix`
--
ALTER TABLE `infospix`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `infovirtual`
--
ALTER TABLE `infovirtual`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `ipsblock`
--
ALTER TABLE `ipsblock`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `layout`
--
ALTER TABLE `layout`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `links`
--
ALTER TABLE `links`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `onlines`
--
ALTER TABLE `onlines`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `pix`
--
ALTER TABLE `pix`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `pixel`
--
ALTER TABLE `pixel`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `pixel_meta`
--
ALTER TABLE `pixel_meta`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `pixel_tiktok`
--
ALTER TABLE `pixel_tiktok`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `relatorio`
--
ALTER TABLE `relatorio`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `relatorio_gateway`
--
ALTER TABLE `relatorio_gateway`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `relatorio_gateway2`
--
ALTER TABLE `relatorio_gateway2`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `smtp`
--
ALTER TABLE `smtp`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `tarifas`
--
ALTER TABLE `tarifas`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `tiktok`
--
ALTER TABLE `tiktok`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `token_zap`
--
ALTER TABLE `token_zap`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `utmify`
--
ALTER TABLE `utmify`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `inclusos`
--
ALTER TABLE `inclusos`
  ADD CONSTRAINT `fk_tarifa` FOREIGN KEY (`tarifa_id`) REFERENCES `tarifas` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
