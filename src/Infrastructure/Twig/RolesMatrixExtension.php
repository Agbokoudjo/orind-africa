<?php

declare(strict_types=1);

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Infrastructure\Twig;

use App\Infrastructure\Security\RolesBuilder\MatrixRolesBuilderInterface;
use Symfony\Component\Form\FormView;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * @author Christian Gripp <mail@core23.de>
 * @author Cengizhan Çalışkan <cengizhancaliskan@gmail.com>
 * @author Silas Joisten <silasjoisten@hotmail.de>
 */
final class RolesMatrixExtension extends AbstractExtension
{
    public function __construct(private MatrixRolesBuilderInterface $rolesBuilder)
    {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('renderMatrix', $this->renderMatrix(...), ['needs_environment' => true]),
            new TwigFunction(
                'renderRolesList',
                $this->renderRolesList(...),
                ['needs_environment' => true]
            ),
        ];
    }

    public function renderRolesList(Environment $environment, FormView $form): string
    {
        $roles = $this->rolesBuilder->getRoles();
        foreach ($roles as $role => $attributes) {
            if (isset($attributes['admin_label'])) {
                unset($roles[$role]);
                continue;
            }

            $roles[$role] = $attributes;
            foreach ($form->getIterator() as $child) {
                \assert(null !== $child);
                if ($child->vars['value'] === $role) {
                    $roles[$role]['form'] = $child;
                }
            }
        }

        return $environment->render('bundles/SonataUserBundle/Form/roles_matrix_list.html.twig', [
            'roles' => $roles,
        ]);
    }

    public function renderMatrix(Environment $environment, FormView $form): string
    {
        $groupedRoles = [];
        foreach ($this->rolesBuilder->getRoles() as $role => $attributes) {
            if (!isset($attributes['admin_code'])) {
                 continue;
            }

            $groupCode = $attributes['group_code'] ?? '';
            $groupedRoles[$groupCode][$attributes['admin_code']][$role] = $attributes;
            foreach ($form->getIterator() as $child) {
                \assert(null !== $child);
                if ($child->vars['value'] === $role) {
                    $groupedRoles[$groupCode][$attributes['admin_code']][$role]['form'] = $child;
                }
            }
        }
        
        return $environment->render('bundles/SonataUserBundle/Form/roles_matrix.html.twig', [
            'grouped_roles' => $groupedRoles,
            'permission_labels' => $this->rolesBuilder->getPermissionLabels(),
        ]);
    }
}
