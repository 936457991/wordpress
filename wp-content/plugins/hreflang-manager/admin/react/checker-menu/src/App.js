import Table from './components/Table';
import RefreshIcon from '../../../assets/img/icons/refresh-cw-01.svg';
import LoadingScreen from "../../shared-components/LoadingScreen";

const useState = wp.element.useState;
const useEffect = wp.element.useEffect;

const {__} = wp.i18n;

const App = () => {

    const [formData, setFormData] = useState(
        {
            searchString: '',
            searchStringChanged: false,
            sortingColumn: 'date',
            sortingOrder: 'desc'
        }
    );

    const [dataAreLoading, setDataAreLoading] = useState(true);

    const [dataUpdateRequired, setDataUpdateRequired] = useState(false);

    const [tableData, setTableData] = useState([]);
    const [statistics, setStatistics] = useState({
        allPosts: 0,
        SuccessfulResponses: 0,
        totalUrlCounter: 0,
        queueUrlCount: 0,
        queueUrlCountChecked: 0,
        queueAlternateUrlCount: 0,
        queueAlternateUrlCountChecked: 0
    });

    useEffect(() => {

        setDataAreLoading(true);

        /**
         * Initialize the chart data with the data received from the REST API
         * endpoint provided by the plugin.
         */
        wp.apiFetch({
            path: '/daext-hreflang-manager/v1/hreflang-checker-issues',
            method: 'POST',
            data: {
                search_string: formData.searchString,
                sorting_column: formData.sortingColumn,
                sorting_order: formData.sortingOrder,
                data_update_required: dataUpdateRequired
            }
        }).then(data => {

                // Set the table data with setTableData().
                setTableData(data.table);

                // Set the statistics.
                setStatistics({
                    allPosts: data.hreflang_checker_issues.all_issues,
                    totalUrlCounter: data.hreflang_checker_issues.total_url_counter,
                    queueUrlCount: data.hreflang_checker_issues.queue_url_count,
                    queueUrlCountChecked: data.hreflang_checker_issues.queue_url_count_checked,
                    queueAlternateUrlCount: data.hreflang_checker_issues.queue_alternate_url_count,
                    queueAlternateUrlCountChecked: data.hreflang_checker_issues.queue_alternate_url_count_checked
                });

                if (dataUpdateRequired) {

                    // Set the dataUpdateRequired state to false.
                    setDataUpdateRequired(false);

                    // Set the form data to the initial state.
                    setFormData({
                        searchString: '',
                        searchStringChanged: false,
                        sortingColumn: 'date',
                        sortingOrder: 'desc'
                    });

                }

                setDataAreLoading(false);

            },
        );

    }, [
        formData.searchStringChanged,
        formData.sortingColumn,
        formData.sortingOrder,
        dataUpdateRequired
    ]);

    /**
     * Function to handle key press events.
     *
     * @param event
     */
    function handleKeyUp(event) {

        // Check if Enter key is pressed (key code 13)
        if (event.key === 'Enter') {
            event.preventDefault(); // Prevent form submission.
            document.getElementById('dahm-search-button').click(); // Simulate click on search button.
        }

    }

    /**
     * Used by the Navigation component.
     *
     * @param e
     */
    function handleSortingChanges(e) {

        /**
         * Check if the sorting column is the same as the previous one.
         * If it is, change the sorting order.
         * If it is not, change the sorting column and set the sorting order to 'asc'.
         */
        let sortingOrder = formData.sortingOrder;
        if (formData.sortingColumn === e.target.value) {
            sortingOrder = formData.sortingOrder === 'asc' ? 'desc' : 'asc';
        }

        setFormData({
            ...formData,
            sortingColumn: e.target.value,
            sortingOrder: sortingOrder
        })

    }

    /**
     * Used to toggle the dataUpdateRequired value.
     *
     * @param e
     */
    function handleDataUpdateRequired(e) {
        setDataUpdateRequired(prevDataUpdateRequired => {
            return !prevDataUpdateRequired;
        });
    }

    return (

        <>

            <React.StrictMode>

                {
                    !dataAreLoading ?

                        <div className={'dahm-react-table'}>

                                <div className={'dahm-react-table-header'}>
                                    <div className={'statistics'}>
                                        <div className={'statistic-label'}>{__('All issues', 'hreflang-manager')}:</div>
                                        <div className={'statistic-value'}>{statistics.allPosts}</div>
                                        <div className={'statistic-label'}>Status:</div>
                                        <div className={'statistic-value'}>{
                                            parseInt(statistics.queueUrlCount, 10) === parseInt(statistics.queueUrlCountChecked, 10) ? 'Completed' : 'In progress (' + parseInt(statistics.queueUrlCountChecked, 10) + ' ' + 'URLs checked,' + ' ' + parseInt(statistics.queueUrlCount, 10) + ' discovered)'
                                        }</div>
                                    </div>
                                    <div className={'tools-actions'}>
                                        <button
                                            onClick={(event) => handleDataUpdateRequired(event)}
                                        ><img src={RefreshIcon} className={'button-icon'}></img>
                                            {__('Check Hreflang', 'hreflang-manager')}
                                        </button>
                                    </div>
                                </div>

                                <div className={'dahm-react-table__dahm-filters'}>

                                    <div className={'dahm-search-container'}>
                                        <input onKeyUp={handleKeyUp} type={'text'}
                                               placeholder={__('Search URLs, issues, or details', 'hreflang-manager')}
                                               value={formData.searchString}
                                               onChange={(event) => setFormData({
                                                   ...formData,
                                                   searchString: event.target.value
                                               })}
                                        />
                                        <input id={'dahm-search-button'} className={'dahm-btn dahm-btn-secondary'}
                                               type={'submit'} value={__('Search', 'hreflang-manager')}
                                               onClick={() => setFormData({
                                                   ...formData,
                                                   searchStringChanged: formData.searchStringChanged ? false : true
                                               })}
                                        />
                                    </div>

                                </div>

                                <Table
                                    data={tableData}
                                    handleSortingChanges={handleSortingChanges}
                                    formData={formData}
                                />

                            </div>

                        :
                        <LoadingScreen
                            loadingDataMessage={__('Loading data...', 'hreflang-manager')}
                            generatingDataMessage={__('Data is being generated. For large sites, this process may take several minutes. Please wait...', 'hreflang-manager')}
                            dataUpdateRequired={dataUpdateRequired}/>
                }

            </React.StrictMode>

        </>

    );

};
export default App;