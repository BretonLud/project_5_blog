<?php

namespace App\Controller\Admin;

use App\ClassAbstract\AbstractController;
use App\Entity\Blog;
use App\Entity\Picture;
use App\Http\RedirectResponse;
use App\Http\Response;
use App\Repository\BlogRepository;
use App\Repository\CommentRepository;
use App\Repository\PictureRepository;
use App\Service\SlugService;
use Exception;
use Respect\Validation\Exceptions\NestedValidationException;
use Respect\Validation\Validatable;
use Respect\Validation\Validator as v;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class BlogController extends AbstractController
{
    
    private BlogRepository $blogRepository;
    private PictureRepository $pictureRepository;
    private const UPLOAD_DIR = ROOT . "/public/pictures/";
    private const PICTURES_KEY = 'pictures';
    private const NAME_KEY = 'name';
    private CommentRepository $commentRepository;
    
    public function __construct()
    {
        parent::__construct();
        $this->blogRepository = new BlogRepository();
        $this->pictureRepository = new PictureRepository();
        $this->commentRepository = new CommentRepository();
    }
    
    /**
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws LoaderError
     * @throws Exception
     */
    public function index(): Response
    {
        $this->isGrantedAdmin($this->getUser());
        
        $blogs = $this->blogRepository->findAll();
        
        $html = $this->twig->render('admin/blog/index.html.twig', context: [
            'blogs' => $blogs
        ]);
        
        return new Response($html);
    }
    
    
    /**
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     * @throws Exception
     */
    public function create(): Response|RedirectResponse
    {
        $response = $this->preHandle('create');
        
        if (!is_null($response)) {
            return $response;
        }
        
        $html = $this->twig->render('admin/blog/create.html.twig');
        
        return new Response($html);
    }
    
    /**
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws LoaderError
     * @throws Exception
     */
    public function edit(string $slug): Response|RedirectResponse
    {
        $blog = $this->blogRepository->findBySlug($slug);
        
        if ($this->getUser() != $blog->getUser()) {
            $_SESSION['errors'][] = "Impossible de modifier un blog qui n'est pas à vous.";
            return new RedirectResponse('/admin/blog');
        }
        
        $response = $this->preHandle('edit', $blog);
        
        if (!is_null($response)) {
            return $response;
        }
        
        $html = $this->twig->render('admin/blog/edit.html.twig', context: [
            'blog' => $blog
        ]);
        
        return new Response($html);
    }
    
    /**
     * @throws Exception
     */
    public function delete(string $slug): RedirectResponse
    {
        $this->isGrantedAdmin($this->getUser());
        
        $blog = $this->blogRepository->findBySlug($slug);
        
        if ($this->getUser() != $blog->getUser()) {
            $_SESSION['errors'][] = "Impossible de supprimer un blog qui n'est pas à vous.";
            return new RedirectResponse('/admin/blogs');
        }
        
        $pictures = $blog->getPictures();
        foreach ($pictures as $picture) {
            $name = $picture->getName();
            $path = ROOT . "/public/pictures/$name";
            if (file_exists($path)) {
                unlink($path);
            }
            $this->pictureRepository->delete($picture);
        }
        
        $this->commentRepository->deleteByBlog($blog);
        
        $this->blogRepository->delete($blog);
        
        $_SESSION['success'][] = 'Blog supprimé';
        
        return new RedirectResponse('/admin/blog');
    }
    
    /**
     * @throws Exception
     */
    private function preHandle(string $action, ?Blog $blog = null): ?RedirectResponse
    {
        $this->isGrantedAdmin($this->getUser());
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            
            if (empty($_FILES)) {
                $_SESSION['errors'][] = "Vous ne pouvez pas supprimer l'image de base !";
                return new RedirectResponse('/admin/blog/create');
            }
            
            
            if (!$this->validatePictures($_FILES)) {
                return new RedirectResponse('/admin/blog/create');
            }
            
            if ($action === 'edit') {
                $processResult = $this->processAndValidateImages($blog);
                
                if ($processResult instanceof RedirectResponse) {
                    return $processResult;
                }
            }
            
            
            $validator = $this->validate();
            
            try {
                
                $validator->assert($_POST);
                
                if ($action === 'edit') {
                    
                    $blog->setTitle($_POST['title']);
                    $blog->setContent($_POST['content']);
                    $blog->setUpdatedAt(new \DateTime());
                    if (strlen($_POST['content']) > 252) {
                        $summary = substr($_POST['content'], 0, 252) . '...';
                    } else {
                        $summary = $_POST['content'];
                    }
                    
                    $blog->setSummary($summary);
                    (new SlugService($blog->getTitle(), $this->blogRepository, $blog))->updateSlug($blog->getId());
                    $this->blogRepository->update($blog);
                    $_SESSION['success'][] = 'Blog modifié';
                }
                
                if ($action === 'create') {
                    $blog = $this->setBlog($_POST);
                    
                    $blog = $this->blogRepository->create($blog);
                    
                    $this->uploadImages($_FILES['pictures'], $blog, $_POST);
                    
                    $_SESSION['success'][] = 'Blog créé';
                }
                
                return new RedirectResponse('/admin/blog');
                
            } catch (NestedValidationException $exception) {
                $validationErrors = $exception->getMessages();
                $_SESSION['error'] = $validationErrors;
                
                $_SESSION['title'] = $_POST['title'];
                $_SESSION['content'] = $_POST['content'];
                
                if ($action === 'edit') {
                    $slug = $blog->getSlug();
                    return new RedirectResponse("/admin/blog/edit/$slug");
                }
                return new RedirectResponse("/admin/blog/create");
            }
            
        }
        
        return null;
    }
    
    private function validatePictures(array $pictures): bool
    {
        $hasErrors = false;
        
        $validator = v::image()->size(null, '2MB')->setTemplate("L'image doit faire au maximum 2MB est doit être de type: jpeg,png ou webp");
        
        foreach ($pictures['pictures']['tmp_name'] as $key => $picture) {
            
            if ($pictures['pictures']['error'][$key] == UPLOAD_ERR_NO_FILE) {
                continue;
            }
            
            if (!file_exists($picture)) {
                continue;
            }
            
            $fileInfo = new \SplFileInfo($picture);
            
            try {
                $validator->assert($fileInfo);
            } catch (NestedValidationException $e) {
                $hasErrors = true;
                $_SESSION['error']['pictures'][$key] = $e->getMessages();
            }
            
        }
        
        return !$hasErrors;
    }
    
    private function uploadImages(array $pictures, Blog $blog, array $datas): void
    {
        foreach ($pictures['tmp_name'] as $key => $tmpName) {
            
            if (empty($tmpName)) {
                continue;
            }
            
            if (!file_exists($tmpName)) {
                continue;
            }
            
            $name = uniqid("", true) . '_' . $pictures["name"][$key];
            $picture = new Picture();
            $picture->setBlog($blog);
            $picture->setName($name);
            
            if (isset($datas['pictures'][$key]['header'])) {
                $picture->setHeader($datas['pictures'][$key]['header']);
            }
            
            (new SlugService($name, $this->pictureRepository, $picture))->updateSlug();
            $this->pictureRepository->createImageForBlog($picture);
            
            try {
                move_uploaded_file($tmpName, self::UPLOAD_DIR . $name);
            } catch (Exception $e) {
                $_SESSION['errors'][] = $e->getMessage();
            }
            
        }
    }
    
    /**
     * @throws Exception
     */
    private function processAndValidateImages(Blog $blog): RedirectResponse|true
    {
        foreach ($blog->getPictures() as $index => $picture) {
            
            $this->handlePictureUpdate($index, $picture);
            
            
            if (!$this->validatePictures($_FILES)) {
                return new RedirectResponse('/admin/blog/edit/' . $blog->getSlug(), ['blog' => $blog]);
            }
        }
        
        $this->uploadImages($_FILES['pictures'], $blog, $_POST);
        return true;
    }
    
    /**
     * @throws Exception
     */
    private function handlePictureUpdate(int $index, Picture $picture): void
    {
        $picturesData = $_FILES[self::PICTURES_KEY][self::NAME_KEY];
        
        if (!array_key_exists($index, $picturesData)) {
            $this->deletePicture($picture);
            return;
        }
        
        if (isset($_POST[self::PICTURES_KEY][$index]['header'])) {
            $picture->setHeader($_POST[self::PICTURES_KEY][$index]['header']);
            $this->pictureRepository->updateHeader($picture);
        }
        
        if ($picture->getHeader() && !isset($_POST[self::PICTURES_KEY][$index]['header'])) {
            $picture->setHeader(false);
            $this->pictureRepository->updateHeader($picture);
        }
        
        if ($picturesData[$index] === $picture->getName() || !$this->validatePictures($_FILES) || empty($picturesData[$index])) {
            return;
        }
        
        try {
            unlink(self::UPLOAD_DIR . $picture->getName());
        } catch (Exception $e) {
            $_SESSION['errors'][] = $e->getMessage();
        }
       
        
        $tmpName = $_FILES[self::PICTURES_KEY]['tmp_name'][$index];
        $name = uniqid("", true) . '_' . $picturesData[$index];
        
        (new SlugService($name, $this->pictureRepository, $picture))->updateSlug();
        $picture->setName($name);
        if (isset($_POST[self::PICTURES_KEY][$index]['header']))
            $picture->setHeader($_POST[self::PICTURES_KEY][$index]['header']);
        
        $this->pictureRepository->update($picture);
        
        try {
            move_uploaded_file($tmpName, self::UPLOAD_DIR . $name);
        } catch (Exception $e) {
            $_SESSION['errors'][] = $e->getMessage();
        }
        
    }
    
    private function deletePicture($picture): void
    {
        unlink(ROOT . "/public/pictures/" . $picture->getName());
        $this->pictureRepository->delete($picture);
    }
    
    /**
     * @return Validatable
     */
    private function validate(): Validatable
    {
        return v::key('title', v::notBlank()->stringType()->regex("/^[a-z0-9\s\-àâäéèêëïîôöùûüÿç.'?!,:;()]*$/iu")->setTemplate('Le titre est nécessaire et doit être composé de lettres et chiffre'))
            ->key('content', v::notBlank()->stringType()->regex('/^[^<>]*$/')->setTemplate('Le texte est requis et ne doit pas être composé des symboles < et >'));
    }
    
    /**
     * @param array $post
     * @return Blog
     */
    private function setBlog(array $post): Blog
    {
        $blog = new Blog();
        $blog->setTitle($post['title']);
        $blog->setContent($post['content']);
        
        if (strlen($post['content']) > 252) {
            $summary = substr($post['content'], 0, 252) . '...';
        } else {
            $summary = $post['content'];
        }
        
        $blog->setSummary($summary);
        
        (new SlugService($blog->getTitle(), $this->blogRepository, $blog))->updateSlug();
        $blog->setUser($this->getUser());
        
        return $blog;
    }
    
}