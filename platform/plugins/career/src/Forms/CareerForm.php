<?php

namespace Botble\Career\Forms;

use Botble\Base\Forms\FormAbstract;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Career\Http\Requests\CareerRequest;
use Botble\Career\Models\Career;

class CareerForm extends FormAbstract
{

    /**
     * @return mixed|void
     * @throws \Throwable
     */
    public function buildForm()
    {
        $this
            ->setupModel(new Career)
            ->setValidatorClass(CareerRequest::class)
            ->withCustomFields()
            ->add('name', 'text', [
                'label' => trans('core/base::forms.name'),
                'label_attr' => ['class' => 'control-label required'],
                'attr' => [
                    'placeholder'  => trans('core/base::forms.name_placeholder'),
                    'data-counter' => 120,
                ],
            ])
            ->add('location', 'text', [
                'label' => trans('plugins/career::career.location'),
                'label_attr' => ['class' => 'control-label required'],
                'attr' => [
                    'data-counter' => 120,
                ],
            ])
            ->add('salary', 'text', [
                'label' => 'Price',
                'label_attr' => ['class' => 'control-label required'],
                'attr' => [
                    'data-counter' => 120,
                ],
            ])
            ->add('icon', 'mediaImage', [
                'label' => 'Icon',
                'label_attr' => ['class' => 'control-label required'],
                'attr' => [
                    'data-counter' => 120,
                ],
            ])
            ->add('banner[]', 'mediaImages', [
                'label' => 'Banner',
                'label_attr' => ['class' => 'control-label required'],
                'attr' => [
                    'data-counter' => 120,
                ],
                'values'     => $this->getModel()->id ? $this->getModel()->banner : [],
            ])
            ->add('badge', 'text', [
                'label' => 'Badge',
                'label_attr' => ['class' => 'control-label'],
                'attr' => [
                    'data-counter' => 120,
                ],
            ])
            ->add('phone', 'text', [
                'label' => 'Phone Number',
                'label_attr' => ['class' => 'control-label'],
                'attr' => [
                    'data-counter' => 120,
                ],
            ])
            ->add('description', 'textarea', [
                'label'      => trans('core/base::forms.description'),
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'rows'         => 4,
                    'placeholder'  => trans('core/base::forms.description_placeholder'),
                    'data-counter' => 400,
                ],
            ])
            ->add('lists', 'repeater', [
                'id' => 'lists',
                'label'      => 'Add People/Agencies',
                'label_attr' => ['class' => 'control-label mb-2'],
                'wrapper'    => [
                    'class' => 'form-control mb-3 col-md-6',
                ],
                'fields' => [
                        [
                            'label' => 'Image',
                            'type' => 'mediaImage',
                            'wrapper'    => [
                                'class' => 'form-control mb-3 col-md-6',
                            ],
                            'attributes' => [
                                'name' => 'img',
                                'value'   => '',
                                'wrapper'    => [
                                    'class' => 'form-control mb-3 col-md-6',
                                ],
                            ]
                        ],
                        [
                            'label' => 'Name',
                            'type' => 'text',
                            'wrapper'    => [
                                'class' => 'form-control mb-3 col-md-6',
                            ],
                            'attributes' => [
                                'name' => 'name',
                                'value'   => '',
                                'wrapper'    => [
                                    'class' => 'form-control mb-3 col-md-6',
                                ],
                            ]
                        ],
                        [
                            'label' => 'Price',
                            'type' => 'text',
                            'wrapper'    => [
                                'class' => 'form-control mb-3 col-md-6',
                            ],
                            'attributes' => [
                                'name' => 'price',
                                'value'   => '',
                                'wrapper'    => [
                                    'class' => 'form-control mb-3 col-md-6',
                                ],
                            ]
                        ],
                        [
                            'label' => 'Location',
                            'type' => 'text',
                            'wrapper'    => [
                                'class' => 'form-control mb-3 col-md-6',
                            ],
                            'attributes' => [
                                'name' => 'location',
                                'value'   => '',
                                'wrapper'    => [
                                    'class' => 'form-control mb-3 col-md-6',
                                ],
                            ]
                        ],
                        [
                            'label' => 'Description',
                            'type' => 'textarea',
                            'wrapper'    => [
                                'class' => 'form-control mb-3 col-md-6',
                            ],
                            'attributes' => [
                                'name' => 'descriptoin',
                                'value'   => '',
                                'wrapper'    => [
                                    'class' => 'form-control mb-3 col-md-6',
                                ],
                            ]
                        ],
                        [
                            'label' => 'Phone number',
                            'type' => 'text',
                            'wrapper'    => [
                                'class' => 'form-control mb-3 col-md-6',
                            ],
                            'attributes' => [
                                'name' => 'phone',
                                'value'   => '',
                                'wrapper'    => [
                                    'class' => 'form-control mb-3 col-md-6',
                                ],
                            ]
                        ]
                ]
            ])
            ->add('content', 'editor', [
                'label'      => trans('core/base::forms.content'),
                'label_attr' => ['class' => 'control-label required'],
                'attr'       => [
                    'rows'            => 4,
                    'placeholder'     => trans('core/base::forms.description_placeholder'),
                    'with-short-code' => true,
                ],
            ])
            ->add('status', 'select', [
                'label'      => trans('core/base::tables.status'),
                'label_attr' => ['class' => 'control-label required'],
                'attr' => [
                    'class' => 'form-control select-full',
                ],
                'choices'    => BaseStatusEnum::labels(),
            ])
            ->setBreakFieldPoint('status');
    }
}
