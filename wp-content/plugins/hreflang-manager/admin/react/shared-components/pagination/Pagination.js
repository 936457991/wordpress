import React from 'react';
import {usePagination, DOTS} from './usePagination';
import {ReactComponent as ChevronLeftDouble} from '../../../assets/img/icons/chevron-left-double.svg';
import {ReactComponent as ChevronLeft} from '../../../assets/img/icons/chevron-left.svg';
import {ReactComponent as ChevronRight} from '../../../assets/img/icons/chevron-right.svg';
import {ReactComponent as ChevronRightDouble} from '../../../assets/img/icons/chevron-right-double.svg';

const Pagination = props => {
    const {
        onPageChange,
        totalCount,
        siblingCount = 1,
        currentPage,
        pageSize
    } = props;

    const paginationRange = usePagination({
        currentPage,
        totalCount,
        siblingCount,
        pageSize
    });

    if (currentPage === 0 || paginationRange.length < 2) {
        return null;
    }

    const onFirst = () => {
        onPageChange(1);
    };

    const onNext = () => {
       if(currentPage === lastPage){return;}
        onPageChange(currentPage + 1);
    };

    const onPrevious = () => {
        if(currentPage === 1){return;}
        onPageChange(currentPage - 1);
    };

    const onLast = () => {
        onPageChange(lastPage);
    };

    let lastPage = paginationRange[paginationRange.length - 1];
    return (
        <ul
            className={'pagination-container'}
        >
            <li
                className={`pagination-item ${currentPage === 1 ? 'disabled' : ''}`}
                onClick={onFirst}
                key={-2}
            >
                <ChevronLeftDouble/>
            </li>
            <li
                className={`pagination-item ${currentPage === 1 ? 'disabled' : ''}`}
                onClick={onPrevious}
                key={-1}
            >
                <ChevronLeft/>
            </li>
            {paginationRange.map(pageNumber => {
                if (pageNumber === DOTS) {
                    return <li className="pagination-item dots" key={crypto.randomUUID()}>&#8230;</li>;
                }

                return (
                    <li
                        key={pageNumber}
                        className={`pagination-item ${pageNumber === currentPage ? 'selected' : ''}`}
                        onClick={() => onPageChange(pageNumber)}
                    >
                        {pageNumber}
                    </li>
                );
            })}
            <li
                className={`pagination-item ${currentPage === lastPage ? 'disabled' : ''}`}
                onClick={onNext}
                key={lastPage + 1}
            >
                <ChevronRight/>
            </li>
            <li
                className={`pagination-item ${currentPage === lastPage ? 'disabled' : ''}`}
                onClick={onLast}
                key={lastPage + 2}
            >
                <ChevronRightDouble/>
            </li>
        </ul>
    );
};

export default Pagination;
