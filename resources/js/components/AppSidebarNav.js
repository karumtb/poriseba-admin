import { computed, defineComponent, h, onMounted, ref, resolveComponent } from 'vue';

import { RouterLink, useRoute } from 'vue-router';

import { CBadge, CSidebarNav, CNavItem, CNavGroup, CNavTitle } from '@coreui/vue';

import getNav from '@/_nav.js';

import simplebar from 'simplebar-vue';
import 'simplebar-vue/dist/simplebar.min.css';

const normalizePath = (path) =>
  decodeURI(path)
    .replace(/#.*$/, '')
    .replace(/(index)?\.(html)$/, '');

const isActiveLink = (route, link) => {
  if (link === undefined || link === null) {
    return false;
  }

  const currentPath = normalizePath(route.path);
  const targetPath = normalizePath(link);

  return currentPath === targetPath;
};

const isActiveItem = (route, item) => {
  if (isActiveLink(route, item.to)) {
    return true;
  }

  if (Array.isArray(item.items)) {
    return item.items.some((child) => isActiveItem(route, child));
  }

  return false;
};

const AppSidebarNav = defineComponent({
  name: 'AppSidebarNav',

  components: {
    CNavItem,
    CNavGroup,
    CNavTitle,
  },



  setup(props) {
    const route = useRoute();

    const firstRender = ref(true);

    /*
     * _nav.js exports a function:
     *
     * export default function getNav(user) {
     *     return [...]
     * }
     *
     * Therefore we must call getNav().
     */
    const user = JSON.parse(localStorage.getItem('user') || '{}')

    const nav = computed(() => getNav(user))


    onMounted(() => {
      firstRender.value = false;
    });

    const renderItem = (item) => {
      /*
       * Navigation Group
       */
      if (Array.isArray(item.items)) {
        return h(
          CNavGroup,
          {
            as: 'div',
            compact: true,

            ...(firstRender.value && {
              visible: item.items.some((child) => isActiveItem(route, child)),
            }),
          },
          {
            togglerContent: () => [
              item.icon
                ? h(resolveComponent('CIcon'), {
                    customClassName: 'nav-icon',
                    name: item.icon,
                  })
                : null,

              item.name,
            ],

            default: () => item.items.map((child) => renderItem(child)),
          }
        );
      }

      /*
       * External Link
       */
      if (item.href) {
        return h(
          resolveComponent(item.component || 'CNavItem'),
          {
            href: item.href,

            ...(item.external
              ? {
                  target: '_blank',
                  rel: 'noopener noreferrer',
                }
              : {}),
          },
          {
            default: () => [
              item.icon
                ? h(resolveComponent('CIcon'), {
                    customClassName: 'nav-icon',
                    name: item.icon,
                  })
                : h(
                    'span',
                    { class: 'nav-icon' },
                    h('span', {
                      class: 'nav-icon-bullet',
                    })
                  ),

              item.name,

              item.external
                ? h(resolveComponent('CIcon'), {
                    class: 'ms-2',
                    name: 'cil-external-link',
                    size: 'sm',
                  })
                : null,

              item.badge
                ? h(
                    CBadge,
                    {
                      class: 'ms-auto',
                      color: item.badge.color,
                      size: 'sm',
                    },
                    {
                      default: () => item.badge.text,
                    }
                  )
                : null,
            ],
          }
        );
      }

      /*
       * Vue Router Link
       */
      if (item.to) {
        return h(
          resolveComponent(item.component || 'CNavItem'),
          {
            active: isActiveLink(route, item.to),
            as: 'li',
          },
          {
            default: () =>
              h(
                RouterLink,
                {
                  to: item.to,
                  class: 'nav-link',
                  activeClass: 'active',
                },
                {
                  default: () => [
                    /*
                     * Icon
                     */
                    item.icon
                      ? h(resolveComponent('CIcon'), {
                          customClassName: 'nav-icon',
                          name: item.icon,
                        })
                      : h(
                          'span',
                          {
                            class: 'nav-icon',
                          },
                          h('span', {
                            class: 'nav-icon-bullet',
                          })
                        ),

                    /*
                     * Name
                     */
                    item.name,

                    /*
                     * Badge
                     */
                    item.badge
                      ? h(
                          CBadge,
                          {
                            class: 'ms-auto',
                            color: item.badge.color,
                            size: 'sm',
                          },
                          {
                            default: () => item.badge.text,
                          }
                        )
                      : null,
                  ],
                }
              ),
          }
        );
      }

      /*
       * Title / Other item
       */
      return h(
        resolveComponent(item.component || 'CNavTitle'),
        {
          as: 'div',
        },
        {
          default: () => item.name,
        }
      );
    };

    /*
     * Sidebar
     */
    return () =>
      h(
        CSidebarNav,
        {
          as: simplebar,
        },
        {
          default: () => (Array.isArray(nav.value) ? nav.value.map((item) => renderItem(item)) : []),
        }
      );
  },
});

export { AppSidebarNav };
