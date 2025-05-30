//Stylesheet
import './editor.css';

//Dependencies
const {__} = wp.i18n;
const {registerPlugin} = wp.plugins;
const {PluginSidebar} = wp.editPost;
const {TextControl, SelectControl, Button, Modal} = wp.components;
const {Component} = wp.element;
const {select, dispatch, registerStore} = wp.data;
const {withState} = wp.compose;

//Import
import utility from './utility.js';
import languages from './languages.js';
import locale from './locale.js';
import script from './script.js';

class Hreflang_Manager extends Component {

  "use strict";

  constructor(props) {

    "use strict";

    super(...arguments);

    let updateObject = {};
    for (let i = 1; i <= DAHM_OPTIONS.connectionsInMenu; i++) {

      //Prepare the values of URLs, languages, script and locale in the state
      updateObject = {
        ...updateObject,
        ['url' + i]: '',
        ['language' + i]: 'en',
        ['script' + i]: '',
        ['locale' + i]: '',
      };

    }

    this.state = {
      ...updateObject,
    };

    //Add the 'Not Assigned' value to the script array
    script.unshift([__('Not Assigned', 'hreflang-manager'), '']);
    
    //Add the 'Not Assigned' value to the locale array
    locale.unshift([__('Not Assigned', 'hreflang-manager'), '']);

    //The list of languages used in Select is generate here only one time for performance reasons
    this.languagesOptions = languages.map(
        (value) => {
          return {
            value: value[1],
            label: value[1] + ' - ' + value[0],
          };
        });

    //The list of script used in Select is generate here only one time for performance reasons
    this.scriptOptions = script.map(
        (value) => {
          return {
            value: value[1],
            label: value[1].length > 0 ? value[1] + ' - ' + value[0] : value[0],
          };
        });
    
    //The list of locale used in Select is generate here only one time for performance reasons
    this.localeOptions = locale.map(
        (value) => {
          return {
            value: value[1],
            label: value[1].length > 0 ? value[1] + ' - ' + value[0] : value[0],
          };
        });

    //Redux Store START ----------------------------------------------------------------------------------------------------

    //First, lets give the "shape" of the store in the initial state object:
    let initialState = {};
    for (let i = 1; i <= DAHM_OPTIONS.connectionsInMenu; i++) {
      initialState = {
        connections: {
          ...initialState.connections,
          ['url' + i]: '',
          ['language' + i]: 'en',
          ['script' + i]: '',
          ['locale' + i]: '',
        },
      };
    }

    //The reducer used to modify the state of the store based on the provided action type and value
    const reducer = (state = initialState, action) => {
      switch (action.type) {

        case 'UPDATE': {
          return {
            ...state,
            connections: {
              ...state.connections,
              ...action.value,
            },
          };
        }

      }

      return state;
    };

    //The actions of the store used to update the data with 'dispatch'
    const actions = {

      //Update the store by sending the "UPDATE" type along with the connection data to the reducer
      update(value) {
        return {
          type: 'UPDATE',
          value: value,
        };
      },

    };

    //The selectors of the store used to retrieve the data with 'select'
    const selectors = {

      //Get all the connection data from the store
      getConnectionData(state) {
        return state.connections;
      },

    };

    //Register the store
    registerStore('hreflang_manager/main_store', {
      reducer,
      actions,
      selectors,
    });

    //Redux Store END ------------------------------------------------------------------------------------------------------

    //Subscribe START --------------------------------------------------------------------------------------------------

    /**
     * Here a subscription is required to detect when the post is saved.
     *
     * When the post is saved use the hreflang-manager endpoint of the Rest API to save the values
     * available in the modal windows (the values of the state of the ConnectionModalWindow component) in the
     * connections database table.
     *
     * (the proper endpoint used to save the data should is registered in PHP)
     *
     */
    let lastModified = '';

    wp.data.subscribe(() => {

      "use strict";

      let postId = wp.data.select('core/editor').getCurrentPost().id;
      let postModifiedIsChanged = false;

      if (typeof wp.data.select('core/editor').getCurrentPost().modified !== 'undefined' &&
          wp.data.select('core/editor').getCurrentPost().modified !== lastModified) {
        lastModified = wp.data.select('core/editor').getCurrentPost().modified;
        postModifiedIsChanged = true;
      }

      /**
       * Update the connection data when:
       *
       * - The post has been saved
       * - This is not an not an autosave
       * - The "lastModified" flag used to detect if the post "modified" date has changed is set to true
       */
      if (wp.data.select('core/editor').isSavingPost() &&
          !wp.data.select('core/editor').isAutosavingPost() &&
          postModifiedIsChanged === true
      ) {

        //get the value
        const connectionData = select('hreflang_manager/main_store').getConnectionData();

        /**
         * Here the following tasks are performed:
         *
         * - Save the connection data with the Rest API
         * - Update the state of the modal window
         * - Update the values in the store
         */
        const postId = wp.data.select('core/editor').getCurrentPost().id;
        wp.apiFetch({
          path: '/daext-hreflang-manager/v1/post/',
          method: 'POST',
          body: JSON.stringify({
            post_id: postId,
            connection_data: connectionData,
          }),
        }).then(
            () => {

              "use strict";

              //Set the values of URLs, languages, script and locale in the state and in the store
              let updateObject = {};
              for (let i = 1; i <= DAHM_OPTIONS.connectionsInMenu; i++) {
                updateObject = {
                  ...updateObject,
                  ['url' + i]: connectionData['url' + i],
                  ['language' + i]: connectionData['language' + i],
                  ['script' + i]: connectionData['script' + i],
                  ['locale' + i]: connectionData['locale' + i],
                };
              }
              this.setState(updateObject);
              dispatch('hreflang_manager/main_store').update(updateObject);

            },
            (err) => {

              return err;

            },
        );

      }

    });

    //Subscribe END ----------------------------------------------------------------------------------------------------

  }

  /**
   * This method is invoked immediately after a component is mounted (inserted
   * into the tree). Initializations that requires DOM nodes should go here. If
   * you need to load data from a remote endpoint, this is a good place to
   * instantiate the network requests.
   *
   * https://reactjs.org/docs/react-component.html#componentdidmount
   */
  componentDidMount() {

    "use strict";

    /**
     * Set the value of the connection modal window by retrieving the hreflang data from the database. If there
     * isn't a record associated with this post retrieve the hreflang data from the plugin options.
     */
    const postId = wp.data.select('core/editor').getCurrentPost().id;
    wp.apiFetch({
      path: '/daext-hreflang-manager/v1/post/' + postId,
      method: 'GET',
    }).then(
        (databaseData) => {

          "use strict";

          if (databaseData !== false) {

            /**
             * Set the values of URLs, languages and locale in the state and in the store by using the record
             * stored in the database.
             */
            let updateObject = {};
            for (let i = 1; i <= DAHM_OPTIONS.connectionsInMenu; i++) {
              updateObject = {
                ...updateObject,
                ['url' + i]: databaseData['url' + i],
                ['language' + i]: databaseData['language' + i],
                ['script' + i]: databaseData['script' + i],
                ['locale' + i]: databaseData['locale' + i],
              };
            }
            this.setState(updateObject);
            dispatch('hreflang_manager/main_store').update(updateObject);

          } else {

            /**
             * Set the values of URLs, languages, script and locale in the state and in the store by using the plugin
             * options available in the "Defaults" tab.
             */
            wp.apiFetch({
              path: '/daext-hreflang-manager/v1/read-options/',
              method: 'POST',
            }).then(
                (optionsData) => {

                  "use strict";

                  /**
                   * Set the values of URLs, languages, script and locale in the state and in the store by using the default
                   * values available in the options.
                   */
                  let updateObject = {};
                  for (let i = 1; i <= DAHM_OPTIONS.connectionsInMenu; i++) {
                    updateObject = {
                      ...updateObject,
                      ['url' + i]: '',
                      ['language' + i]: optionsData['da_hm_default_language_' + i],
                      ['script' + i]: optionsData['da_hm_default_script_' + i],
                      ['locale' + i]: optionsData['da_hm_default_locale_' + i],
                    };
                  }
                  this.setState(updateObject);
                  dispatch('hreflang_manager/main_store').update(updateObject);

                },
                (err) => {

                  return err;

                },
            );

          }

        },
        (err) => {

          return err;

        },
    );

  }

  render() {

    "use strict";

    //Prepare the data that should be passed to withState() as props
    let callingArray = [];
    let connectionObject = {};
    for (let i = 1; i <= DAHM_OPTIONS.connectionsInMenu; i++) {
      callingArray.push(i);
      connectionObject = {
        ...connectionObject,
        ['url' + i]: this.state['url' + i],
        ['language' + i]: this.state['language' + i],
        ['script' + i]: this.state['script' + i],
        ['locale' + i]: this.state['locale' + i],
      };
    }

    const ConnectionModalWindow = withState({
      scriptOptions: this.scriptOptions,
      localeOptions: this.localeOptions,
      languagesOptions: this.languagesOptions,
      connection: connectionObject,
      callingArray: callingArray,
      isOpen: false,
    })(({
          scriptOptions,
          localeOptions,
          languagesOptions,
          connection,
          callingArray,
          isOpen,
          setState,
        }) => (
        <div>
          <Button className='dahm-set-connection' onClick={() => setState({isOpen: true})}>

            <div className='dahm-set-connection-container'>
              <div className='dahm-set-connection-text'>{__('Edit Connection', 'hreflang-manager')}</div>
              <span className="dashicons dashicons-edit dahm-set-connection-icon"></span>
            </div>
          </Button>
          {isOpen ?
              <Modal
                  title={__('Edit Connection', 'hreflang-manager')}
                  onRequestClose={() => {
                    setState({isOpen: false});
                  }}
                  className='dahm-modal'
              >
                {callingArray.map((index) =>
                    <div className='dahm-single-connection' key={index}>
                      <TextControl
                          autoComplete='off'
                          label={__('URL', 'hreflang-manager') + String.fromCharCode(160) + index}
                          value={connection['url' + index]}
                          onChange={(value) => {
                            setState({
                              connection: {
                                ...connection,
                                ['url' + index]: value,
                              },
                            });
                            dispatch('hreflang_manager/main_store').update({['url' + index]: value});
                            utility.activateUpdateButton();
                          }}
                      />
                      <SelectControl
                          label={__('Language', 'hreflang-manager') + String.fromCharCode(160) + index}
                          value={connection['language' + index]}
                          onChange={(value) => {
                            setState({
                              connection: {
                                ...connection,
                                ['language' + index]: value,
                              },
                            });
                            dispatch('hreflang_manager/main_store').update({['language' + index]: value});
                            utility.activateUpdateButton();
                          }}
                          options={languagesOptions}
                      />
                      <SelectControl
                          label={__('Script', 'hreflang-manager') + String.fromCharCode(160) + index}
                          value={connection['script' + index]}
                          onChange={(value) => {
                            setState({
                              connection: {
                                ...connection,
                                ['script' + index]: value,
                              },
                            });
                            dispatch('hreflang_manager/main_store').update({['script' + index]: value});
                            utility.activateUpdateButton();
                          }}
                          options={scriptOptions}
                      />
                      <SelectControl
                          label={__('Locale', 'hreflang-manager') + String.fromCharCode(160) + index}
                          value={connection['locale' + index]}
                          onChange={(value) => {
                            setState({
                              connection: {
                                ...connection,
                                ['locale' + index]: value,
                              },
                            });
                            dispatch('hreflang_manager/main_store').update({['locale' + index]: value});
                            utility.activateUpdateButton();
                          }}
                          options={localeOptions}
                      />
                    </div>,
                )}
              </Modal>
              : null}
        </div>
    ));

    const icon = (
        <svg id="globe" xmlns="http://www.w3.org/2000/svg" version="1.1" viewBox="0 0 40 40">
              <defs>
        </defs>
          <path d="M38,20c0-9.4-7.3-17.2-16.5-17.9-.5,0-1,0-1.5,0s-1,0-1.5,0C9.3,2.8,2,10.6,2,20s7.3,17.2,16.5,17.9c.5,0,1,0,1.5,0s1,0,1.5,0c9.2-.8,16.5-8.5,16.5-17.9ZM30,19c-.1-2.7-.7-5.2-1.6-7.6,1.3-.5,2.6-1.1,3.8-1.9,2.2,2.6,3.6,5.8,3.9,9.4h-6ZM21,4.4c1.8,1.7,3.4,3.6,4.6,5.8-1.5.4-3,.7-4.6.7v-6.6ZM19,11c-1.6,0-3.1-.3-4.6-.7,1.2-2.2,2.7-4.2,4.6-5.8v6.6ZM19,13v6h-7c.1-2.4.6-4.8,1.5-6.9,1.7.5,3.6.8,5.4.9ZM19,21v6c-1.9,0-3.7.4-5.4.9-.9-2.2-1.4-4.5-1.5-6.9h7ZM19,29v6.6c-1.8-1.7-3.4-3.6-4.6-5.8,1.5-.4,3-.7,4.6-.7ZM21,29c1.6,0,3.1.3,4.6.7-1.2,2.2-2.7,4.2-4.6,5.8v-6.6ZM21,27v-6h7c-.1,2.4-.6,4.8-1.5,6.9-1.7-.5-3.6-.8-5.4-.9ZM21,19v-6c1.9,0,3.7-.4,5.4-.9.9,2.2,1.4,4.5,1.5,6.9h-7ZM27.5,9.6c-.9-1.8-2.1-3.5-3.5-5.1,2.5.6,4.8,1.9,6.6,3.5-1,.6-2,1.1-3.1,1.5ZM12.5,9.6c-1.1-.4-2.1-.9-3.1-1.5,1.9-1.7,4.1-2.9,6.6-3.5-1.4,1.5-2.6,3.2-3.5,5.1ZM11.7,11.4c-.9,2.4-1.5,4.9-1.6,7.6h-6c.2-3.6,1.6-6.9,3.9-9.4,1.2.7,2.4,1.4,3.8,1.9ZM10,21c.1,2.7.7,5.2,1.6,7.6-1.3.5-2.6,1.1-3.8,1.9-2.2-2.6-3.6-5.8-3.9-9.4h6ZM12.5,30.4c.9,1.8,2.1,3.5,3.5,5.1-2.5-.6-4.8-1.9-6.6-3.5,1-.6,2-1.1,3.1-1.5ZM27.5,30.4c1.1.4,2.1.9,3.1,1.5-1.9,1.7-4.1,2.9-6.6,3.5,1.4-1.5,2.6-3.2,3.5-5.1ZM28.3,28.6c.9-2.4,1.5-4.9,1.6-7.6h6c-.2,3.6-1.6,6.9-3.9,9.4-1.2-.7-2.4-1.4-3.8-1.9Z"/>
        </svg>
    );

    return (
        <PluginSidebar
            name='hreflang-manager-sidebar'
            icon={icon}
            title={__('Hreflang Manager', 'hreflang-manager')}
        >
          <div
              className='hreflang-manager-sidebar-content'
          >
            <ConnectionModalWindow/>
          </div>
        </PluginSidebar>
    );

  }

}

registerPlugin('dahm-hreflang-manager', {
  render: Hreflang_Manager,
});