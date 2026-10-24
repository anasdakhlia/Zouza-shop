<?php
/**
 * ZOUZA SHOP - Contrôleur SEO & Sitemap
 *
 * Génère dynamiquement le fichier sitemap.xml pour les moteurs de recherche
 * et sert robots.txt avec l'URL canonique du sitemap.
 */

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/Product.php';
require_once __DIR__ . '/../models/Category.php';

class SitemapController extends Controller
{
    private Product $productModel;
    private Category $categoryModel;

    public function __construct()
    {
        $this->productModel  = new Product();
        $this->categoryModel = new Category();
    }

    /**
     * Génère dynamiquement le sitemap XML respectant le protocole Sitemaps 0.9
     */
    public function index(): void
    {
        $products   = $this->productModel->getAvailable();
        $categories = $this->categoryModel->getAll();

        header('Content-Type: application/xml; charset=utf-8');
        header('X-Robots-Tag: noindex, follow');

        $xml = new XMLWriter();
        $xml->openMemory();
        $xml->setIndent(true);
        $xml->setIndentString('  ');
        $xml->startDocument('1.0', 'UTF-8');

        $xml->startElement('urlset');
        $xml->writeAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');

        // 1. Page d'accueil
        $xml->startElement('url');
        $xml->writeElement('loc', BASE_URL . '/');
        $xml->writeElement('changefreq', 'daily');
        $xml->writeElement('priority', '1.0');
        $xml->endElement();

        // 2. Catalogue public complet
        $xml->startElement('url');
        $xml->writeElement('loc', BASE_URL . '/products');
        $xml->writeElement('changefreq', 'daily');
        $xml->writeElement('priority', '0.9');
        $xml->endElement();

        // 3. Catégories de délices
        foreach ($categories as $cat) {
            $catSlug = !empty($cat['slug']) ? $cat['slug'] : (string)$cat['category_id'];
            $xml->startElement('url');
            $xml->writeElement('loc', BASE_URL . '/products?category=' . urlencode($catSlug));
            $xml->writeElement('changefreq', 'weekly');
            $xml->writeElement('priority', '0.8');
            $xml->endElement();
        }

        // 4. Fiches de chaque produit disponible
        foreach ($products as $prod) {
            $prodUrl = !empty($prod['slug'])
                ? BASE_URL . '/products/detail?slug=' . urlencode($prod['slug'])
                : BASE_URL . '/products/detail?id=' . (int)$prod['product_id'];

            $lastMod = !empty($prod['updated_at'])
                ? date('Y-m-d', strtotime($prod['updated_at']))
                : (!empty($prod['created_at']) ? date('Y-m-d', strtotime($prod['created_at'])) : date('Y-m-d'));

            $xml->startElement('url');
            $xml->writeElement('loc', $prodUrl);
            $xml->writeElement('lastmod', $lastMod);
            $xml->writeElement('changefreq', 'weekly');
            $xml->writeElement('priority', '0.7');
            $xml->endElement();
        }

        $xml->endElement(); // </urlset>
        $xml->endDocument();

        echo $xml->outputMemory();
        exit;
    }

    /**
     * Retourne dynamiquement les directives pour les robots d'indexation
     */
    public function robots(): void
    {
        header('Content-Type: text/plain; charset=utf-8');

        $sitemapUrl = BASE_URL . '/sitemap.xml';

        echo "User-agent: *\n";
        echo "Allow: /\n";
        echo "Allow: /products\n";
        echo "Allow: /assets/\n";
        echo "Allow: /uploads/\n";
        echo "Disallow: /admin\n";
        echo "Disallow: /admin/\n";
        echo "Disallow: /cart\n";
        echo "Disallow: /checkout\n";
        echo "Disallow: /profile\n";
        echo "Disallow: /orders\n";
        echo "Disallow: /login\n";
        echo "Disallow: /logout\n\n";
        echo "Sitemap: {$sitemapUrl}\n";
        exit;
    }
}
