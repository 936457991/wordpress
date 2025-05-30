const useState = wp.element.useState;
import Pagination from '../../../shared-components/pagination/Pagination';

const useMemo = wp.element.useMemo;
const {__} = wp.i18n;

let PageSize = 10;

const Chart = (props) => {

    //Pagination - START --------------------------------------------------------

    const [currentPage, setCurrentPage] = useState(1);

    const currentTableData = useMemo(() => {
        const firstPageIndex = (currentPage - 1) * PageSize;
        const lastPageIndex = firstPageIndex + PageSize;
        return props.data.slice(firstPageIndex, lastPageIndex);
    }, [currentPage, props.data]);

    //Pagination - END ----------------------------------------------------------

    function handleDataIcon(columnName) {

        return props.formData.sortingColumn === columnName ? props.formData.sortingOrder : '';

    }

    return (

        <div className="dahm-data-table-container">

            <table className="dahm-react-table__dahm-data-table dahm-react-table__dahm-data-table-checker-menu">
                <thead>
                <tr>
                    <th>
                        <button
                            className={'dahm-react-table__dahm-sorting-button'}
                            onClick={props.handleSortingChanges}
                            value={'url_to_connect'}
                            data-icon={handleDataIcon('url_to_connect')}
                        >{__('URL to Connect', 'hreflang-manager')}</button>
                    </th>
                    <th>
                        <button
                            className={'dahm-react-table__dahm-sorting-button'}
                            onClick={props.handleSortingChanges}
                            value={'issue_type'}
                            data-icon={handleDataIcon('issue_type')}
                        >{__('Issue', 'hreflang-manager')}</button>
                    </th>
                    <th>
                        <button
                            className={'dahm-react-table__dahm-sorting-button'}
                            onClick={props.handleSortingChanges}
                            value={'details'}
                            data-icon={handleDataIcon('details')}
                        >{__('Details', 'hreflang-manager')}</button>
                    </th>
                    <th>
                        <button
                            className={'dahm-react-table__dahm-sorting-button'}
                            onClick={props.handleSortingChanges}
                            value={'date'}
                            data-icon={handleDataIcon('date')}
                        >{__('Date', 'hreflang-manager')}</button>
                    </th>
                </tr>
                </thead>
                <tbody>

                {currentTableData.map((row) => (
                    <tr key={row.hreflang_checker_issue_id}>
                        <td>
                            <div className={'dahm-react-table__post-cell-container'}>
                                <a href={row.url_to_connect}>{row.url_to_connect}</a>
                                <a href={row.url_to_connect} target={'_blank'}
                                   className={'dahm-react-table__icon-link'}></a>
                            </div>
                        </td>
                        <td>
                            <div className={'dahm-react-table__post-cell-container'}>
                                <div data-severity={row.severity} className={'dahm-react-table__icon-div'}></div>
                                <div>{row.issue_type}</div>
                            </div>
                        </td>
                        <td>
                            <div>{
                                row.alternate_url.length > 0 ?
                                    __('Alternate URL:', 'hreflang-manager') + ' ' + row.alternate_url :
                                    ''
                            }</div>
                            <div>{row.details}</div>
                        </td>
                        <td>{row.formatted_date}</td>
                    </tr>
                ))}

                </tbody>
            </table>
            {props.data.length === 0 && <div
                className="dahm-no-data-found">{__('We couldn\'t find any results matching your filters. Try adjusting your criteria.', 'hreflang-manager')}</div>}
            {props.data.length > 0 &&
                <div className="dahm-react-table__pagination-container">
                    <div className='daext-displaying-num'>{props.data.length + ' items'}</div>
                    <Pagination
                        className="pagination-bar"
                        currentPage={currentPage}
                        totalCount={props.data.length}
                        pageSize={PageSize}
                        onPageChange={page => setCurrentPage(page)}
                    />
                </div>
            }

        </div>

    );

};

export default Chart;
