import { useState } from "react";

const ModuleStep = ({ onNext, initialData = {} }) => {
    const [selectedCard, setSelectedCard] = useState("leave");
    const [formData, setFormData] = useState({
        enableLeaveManagement: initialData.enableLeaveManagement ?? true,
        leaveYears: initialData.leaveYears || [
            {
                id: Date.now(),
                year: "",
                startDate: "",
                endDate: ""
            }
        ],
        workingDays: initialData.workingDays || {
            monday: "full",
            tuesday: "full",
            wednesday: "full",
            thursday: "full",
            friday: "full",
            saturday: "non-working",
            sunday: "non-working"
        },
        workingHours: initialData.workingHours || {
            start: "09:00",
            end: "17:00"
        }
    });

    const handleCardClick = cardType => {
        setSelectedCard(cardType);
    };

    const handleToggle = field => {
        setFormData(prev => ({
            ...prev,
            [field]: !prev[field]
        }));
    };

    const handleDayToggle = (day, value) => {
        setFormData(prev => ({
            ...prev,
            workingDays: {
                ...prev.workingDays,
                [day]: value
            }
        }));
    };

    const handleTimeChange = (field, value) => {
        setFormData(prev => ({
            ...prev,
            workingHours: {
                ...prev.workingHours,
                [field]: value
            }
        }));
    };

    const handleLeaveYearChange = (id, field, value) => {
        setFormData(prev => ({
            ...prev,
            leaveYears: prev.leaveYears.map(year =>
                year.id === id ? { ...year, [field]: value } : year
            )
        }));
    };

    const addLeaveYear = () => {
        setFormData(prev => ({
            ...prev,
            leaveYears: [
                ...prev.leaveYears,
                {
                    id: Date.now(),
                    year: "",
                    startDate: "",
                    endDate: ""
                }
            ]
        }));
    };

    const removeLeaveYear = id => {
        if (formData.leaveYears.length > 1) {
            setFormData(prev => ({
                ...prev,
                leaveYears: prev.leaveYears.filter(year => year.id !== id)
            }));
        }
    };

    const handleSubmit = e => {
        e.preventDefault();
        onNext(formData);
    };

    const days = [
        "monday",
        "tuesday",
        "wednesday",
        "thursday",
        "friday",
        "saturday",
        "sunday"
    ];

    return (
        <div>
            {/* Matches erp-setup-content from setup.css - 640px constraint with auto margins */}
            <div className="max-w-640px mx-auto overflow-visible">
                {/* Heading - matches h1 from setup.css */}
                <h1 className="text-black text-30px font-normal leading-9 text-center m-0 mb-3">
                    Leave and Workday Setup
                </h1>
                {/* Subtitle - matches .subtitle from setup.css */}
                <p className="text-center text-slate-500 text-base m-0 mb-16 leading-6">
                    Configure your leave management and working schedule.
                </p>

                <form onSubmit={handleSubmit} className="mb-0">
                    {/* Card Selection - matches Step 2 pattern */}
                    <div className="flex gap-5 mb-16 px-85px justify-center">
                        {/* Leave Management Card */}
                        <div
                            className={`selection-card ${
                                selectedCard === "leave" ? "selected" : ""
                            }`}
                            onClick={() => handleCardClick("leave")}
                        >
                            <div className="mb-6">
                                <svg
                                    width="30"
                                    height="32"
                                    viewBox="0 0 30 32"
                                    fill="none"
                                    xmlns="http://www.w3.org/2000/svg"
                                >
                                    <path
                                        d="M18.75 11.1992V4.94922C18.75 2.87815 17.0711 1.19922 15 1.19922L5 1.19922C2.92893 1.19922 1.25 2.87815 1.25 4.94922L1.25 27.4492C1.25 29.5203 2.92893 31.1992 5 31.1992H15C17.0711 31.1992 18.75 29.5203 18.75 27.4492V21.1992M23.75 21.1992L28.75 16.1992M28.75 16.1992L23.75 11.1992M28.75 16.1992L7.5 16.1992"
                                        stroke="#0F172A"
                                        strokeWidth="1.5"
                                        strokeLinecap="round"
                                        strokeLinejoin="round"
                                    />
                                </svg>
                            </div>
                            <h3 className="text-base font-medium text-gray-900 m-0">
                                Leave Management
                            </h3>
                        </div>

                        {/* Workday Setup Card */}
                        <div
                            className={`selection-card ${
                                selectedCard === "workday" ? "selected" : ""
                            }`}
                            onClick={() => handleCardClick("workday")}
                        >
                            <div className="mb-6">
                                <svg
                                    width="33"
                                    height="32"
                                    viewBox="0 0 33 32"
                                    fill="none"
                                    xmlns="http://www.w3.org/2000/svg"
                                >
                                    <path
                                        d="M7.75 1.19922V4.94922M25.25 1.19922V4.94922M1.5 27.4492V8.69922C1.5 6.62815 3.17893 4.94922 5.25 4.94922H27.75C29.8211 4.94922 31.5 6.62815 31.5 8.69922V27.4492M1.5 27.4492C1.5 29.5203 3.17893 31.1992 5.25 31.1992H27.75C29.8211 31.1992 31.5 29.5203 31.5 27.4492M1.5 27.4492V14.9492C1.5 12.8782 3.17893 11.1992 5.25 11.1992H27.75C29.8211 11.1992 31.5 12.8782 31.5 14.9492V27.4492M16.5 17.4492H16.5125V17.4617H16.5V17.4492ZM16.5 21.1992H16.5125V21.2117H16.5V21.1992ZM16.5 24.9492H16.5125V24.9617H16.5V24.9492ZM12.75 21.1992H12.7625V21.2117H12.75V21.1992ZM12.75 24.9492H12.7625V24.9617H12.75V24.9492ZM9 21.1992H9.0125V21.2117H9V21.1992ZM9 24.9492H9.0125V24.9617H9V24.9492ZM20.25 17.4492H20.2625V17.4617H20.25V17.4492ZM20.25 21.1992H20.2625V21.2117H20.25V21.1992ZM20.25 24.9492H20.2625V24.9617H20.25V24.9492ZM24 17.4492H24.0125V17.4617H24V17.4492ZM24 21.1992H24.0125V21.2117H24V21.1992Z"
                                        stroke="#0F172A"
                                        strokeWidth="1.5"
                                        strokeLinecap="round"
                                        strokeLinejoin="round"
                                    />
                                </svg>
                            </div>
                            <h3 className="text-base font-medium text-gray-900 m-0">
                                Workday Setup
                            </h3>
                        </div>
                    </div>

                    {/* Leave Management Section */}
                    {selectedCard === "leave" && (
                        <div className="mb-8">
                            {/* Leave Years Section */}
                            <div className="mb-6">
                                {formData.leaveYears.map(leaveYear => (
                                    <div key={leaveYear.id} className="mb-6">
                                        {/* Leave Year Input - Full Width */}
                                        <div className="mb-6">
                                            <label
                                                htmlFor={`leaveYear-${leaveYear.id}`}
                                                className="label"
                                            >
                                                Leave Year
                                            </label>
                                            <div className="flex gap-3 items-start">
                                                <input
                                                    type="text"
                                                    id={`leaveYear-${leaveYear.id}`}
                                                    value={leaveYear.year}
                                                    onChange={e =>
                                                        handleLeaveYearChange(
                                                            leaveYear.id,
                                                            "year",
                                                            e.target.value
                                                        )
                                                    }
                                                    className="input flex-1"
                                                    placeholder="Leave Year"
                                                />
                                                {formData.leaveYears.length >
                                                    1 && (
                                                    <button
                                                        type="button"
                                                        onClick={() =>
                                                            removeLeaveYear(
                                                                leaveYear.id
                                                            )
                                                        }
                                                        className="flex-shrink-0 w-10 h-10 flex items-center justify-center border border-red-500 rounded-md text-red-500 hover:bg-red-50 transition-colors"
                                                        aria-label="Remove leave year"
                                                    >
                                                        <svg
                                                            width="15"
                                                            height="18"
                                                            viewBox="0 0 15 18"
                                                            fill="none"
                                                            xmlns="http://www.w3.org/2000/svg"
                                                        >
                                                            <path
                                                                d="M9.20414 6.69231L8.93787 13.6154M5.25444 13.6154L4.98817 6.69231M12.6559 4.22351C12.9189 4.26324 13.1811 4.30575 13.4423 4.35099M12.6559 4.22351L11.8345 14.902C11.7651 15.8037 11.0132 16.5 10.1088 16.5H4.08352C3.17913 16.5 2.42721 15.8037 2.35784 14.902L1.53642 4.22351M12.6559 4.22351C11.774 4.09034 10.8819 3.98835 9.98077 3.91871M0.75 4.35099C1.01121 4.30575 1.27336 4.26324 1.53642 4.22351M1.53642 4.22351C2.41829 4.09034 3.31038 3.98835 4.21154 3.91871M9.98077 3.91871V3.21399C9.98077 2.30679 9.28027 1.54941 8.37353 1.5204C7.9494 1.50684 7.52358 1.5 7.09615 1.5C6.66873 1.5 6.24291 1.50684 5.81878 1.5204C4.91204 1.54941 4.21154 2.30679 4.21154 3.21399V3.91871M9.98077 3.91871C9.02889 3.84515 8.0669 3.80769 7.09615 3.80769C6.12541 3.80769 5.16342 3.84515 4.21154 3.91871"
                                                                stroke="#EF4444"
                                                                strokeWidth="1.5"
                                                                strokeLinecap="round"
                                                                strokeLinejoin="round"
                                                            />
                                                        </svg>
                                                    </button>
                                                )}
                                            </div>
                                        </div>

                                        {/* Two Column Layout - Start Date and End Date */}
                                        <div className="grid grid-cols-2 gap-5">
                                            {/* Start Date */}
                                            <div className="mb-6">
                                                <label
                                                    htmlFor={`startDate-${leaveYear.id}`}
                                                    className="label"
                                                >
                                                    Start Date
                                                </label>
                                                <input
                                                    type="date"
                                                    id={`startDate-${leaveYear.id}`}
                                                    value={leaveYear.startDate}
                                                    onChange={e =>
                                                        handleLeaveYearChange(
                                                            leaveYear.id,
                                                            "startDate",
                                                            e.target.value
                                                        )
                                                    }
                                                    className="input"
                                                    placeholder="dd/mm/yy"
                                                />
                                            </div>

                                            {/* End Date */}
                                            <div className="mb-6">
                                                <label
                                                    htmlFor={`endDate-${leaveYear.id}`}
                                                    className="label"
                                                >
                                                    End Date
                                                </label>
                                                <input
                                                    type="date"
                                                    id={`endDate-${leaveYear.id}`}
                                                    value={leaveYear.endDate}
                                                    onChange={e =>
                                                        handleLeaveYearChange(
                                                            leaveYear.id,
                                                            "endDate",
                                                            e.target.value
                                                        )
                                                    }
                                                    className="input"
                                                    placeholder="dd/mm/yy"
                                                />
                                            </div>
                                        </div>
                                    </div>
                                ))}

                                {/* Add New Button */}
                                <button
                                    type="button"
                                    onClick={addLeaveYear}
                                    className="inline-flex items-center gap-2 px-5 py-3 text-sm font-medium text-white bg-blue-500 rounded-md hover:bg-blue-600 transition-colors"
                                >
                                    <svg
                                        width="16"
                                        height="16"
                                        viewBox="0 0 16 16"
                                        fill="none"
                                        xmlns="http://www.w3.org/2000/svg"
                                    >
                                        <path
                                            d="M8 3.33333V12.6667M3.33333 8H12.6667"
                                            stroke="currentColor"
                                            strokeWidth="1.5"
                                            strokeLinecap="round"
                                            strokeLinejoin="round"
                                        />
                                    </svg>
                                    Add New
                                </button>
                            </div>

                            {/* Leave Management Toggle */}
                            <div
                                className="rounded-lg p-8 mb-6"
                                style={{
                                    backgroundColor: "#e0f2fe",
                                    border: "1px solid #bfdbfe"
                                }}
                            >
                                <div className="flex items-center gap-4">
                                    <div className="flex-shrink-0">
                                        <input
                                            type="checkbox"
                                            id="enableLeaveManagement"
                                            checked={
                                                formData.enableLeaveManagement
                                            }
                                            onChange={() =>
                                                handleToggle(
                                                    "enableLeaveManagement"
                                                )
                                            }
                                            className="sr-only"
                                        />
                                        <label
                                            htmlFor="enableLeaveManagement"
                                            className="flex items-center cursor-pointer"
                                            style={{
                                                display: "flex",
                                                visibility: "visible"
                                            }}
                                        >
                                            <span
                                                className={`relative inline-block rounded-full transition-colors duration-300`}
                                                style={{
                                                    width: "36px",
                                                    height: "16px",
                                                    backgroundColor: formData.enableLeaveManagement
                                                        ? "#3B82F6"
                                                        : "#E2E8F0",
                                                    display: "inline-block",
                                                    visibility: "visible"
                                                }}
                                            >
                                                <span
                                                    className="absolute rounded-full transition-all duration-300 ease-in-out"
                                                    style={{
                                                        top: "-2px",
                                                        width: "20px",
                                                        height: "20px",
                                                        backgroundColor:
                                                            "#ffffff",
                                                        left: formData.enableLeaveManagement
                                                            ? "17px"
                                                            : "2px",
                                                        boarder:
                                                            "1px solid #CBD5E1",
                                                        boxShadow:
                                                            "0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06)"
                                                    }}
                                                ></span>
                                            </span>
                                        </label>
                                    </div>
                                    <div className="flex-1">
                                        <p className="text-slate-500 text-sm leading-5 m-0">
                                            Generate pre-default leave policies
                                            for the current year (WPERP will
                                            automatically assign predefined
                                            leaves like Sick Leave/Casual leave
                                            to the current year
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    )}

                    {/* Workday Setup Section */}
                    {selectedCard === "workday" && (
                        <div className="mb-8">
                            {/* Working Days */}
                            <div className="mb-6">
                                <label className="mb-4 font-medium text-gray-900 text-lg text-center block">
                                    Leave and Workday Setup
                                </label>
                                {/* Divider line above Leave and Workday Setup */}
                                <div
                                    className="mx-auto"
                                    style={{
                                        width: "640px",
                                        height: "0px",
                                        borderTop: "1px solid #E2E8F0",
                                        opacity: 1,
                                        marginBottom: "24px"
                                    }}
                                ></div>

                                <div className="flex flex-col gap-4">
                                    {days.map(day => (
                                        <div
                                            key={day}
                                            className="flex items-center justify-between py-3"
                                        >
                                            <div className="font-medium min-w-[100px] capitalize text-sm">
                                                {day}
                                            </div>
                                            <div className="flex gap-2">
                                                <label className="inline-block">
                                                    <input
                                                        type="radio"
                                                        name={`day-${day}`}
                                                        checked={
                                                            formData
                                                                .workingDays[
                                                                day
                                                            ] === "full"
                                                        }
                                                        onChange={() =>
                                                            handleDayToggle(
                                                                day,
                                                                "full"
                                                            )
                                                        }
                                                        className="hidden"
                                                    />
                                                    <span
                                                        className={`inline-block px-5 py-3 rounded-md cursor-pointer transition-all duration-200 font-normal text-center ${
                                                            formData
                                                                .workingDays[
                                                                day
                                                            ] === "full"
                                                                ? "bg-blue-500 text-white border border-blue-500"
                                                                : "bg-white text-black border border-gray-300"
                                                        }`}
                                                        style={{
                                                            fontFamily: "Inter",
                                                            fontSize: "16px",
                                                            lineHeight: "16px",
                                                            letterSpacing: "0%",
                                                            boxShadow:
                                                                "0px 1px 2px 0px #0000000D"
                                                        }}
                                                    >
                                                        Full Day
                                                    </span>
                                                </label>
                                                <label className="inline-block">
                                                    <input
                                                        type="radio"
                                                        name={`day-${day}`}
                                                        checked={
                                                            formData
                                                                .workingDays[
                                                                day
                                                            ] === "half"
                                                        }
                                                        onChange={() =>
                                                            handleDayToggle(
                                                                day,
                                                                "half"
                                                            )
                                                        }
                                                        className="hidden"
                                                    />
                                                    <span
                                                        className={`inline-block px-5 py-3 rounded-md cursor-pointer transition-all duration-200 font-normal text-center ${
                                                            formData
                                                                .workingDays[
                                                                day
                                                            ] === "half"
                                                                ? "bg-blue-500 text-white border border-blue-500"
                                                                : "bg-white text-black border border-gray-300"
                                                        }`}
                                                        style={{
                                                            fontFamily: "Inter",
                                                            fontSize: "16px",
                                                            lineHeight: "16px",
                                                            letterSpacing: "0%",
                                                            boxShadow:
                                                                "0px 1px 2px 0px #0000000D"
                                                        }}
                                                    >
                                                        Half Day
                                                    </span>
                                                </label>
                                                <label className="inline-block">
                                                    <input
                                                        type="radio"
                                                        name={`day-${day}`}
                                                        checked={
                                                            formData
                                                                .workingDays[
                                                                day
                                                            ] === "non-working"
                                                        }
                                                        onChange={() =>
                                                            handleDayToggle(
                                                                day,
                                                                "non-working"
                                                            )
                                                        }
                                                        className="hidden"
                                                    />
                                                    <span
                                                        className={`inline-block px-5 py-3 rounded-md cursor-pointer transition-all duration-200 font-normal text-center ${
                                                            formData
                                                                .workingDays[
                                                                day
                                                            ] === "non-working"
                                                                ? "bg-blue-500 text-white border border-blue-500"
                                                                : "bg-white text-black border border-gray-300"
                                                        }`}
                                                        style={{
                                                            fontFamily: "Inter",
                                                            fontSize: "16px",
                                                            lineHeight: "16px",
                                                            letterSpacing: "0%",
                                                            boxShadow:
                                                                "0px 1px 2px 0px #0000000D"
                                                        }}
                                                    >
                                                        Non-working Day
                                                    </span>
                                                </label>
                                            </div>
                                        </div>
                                    ))}
                                </div>
                            </div>
                        </div>
                    )}

                    {/* Divider line above button - only show when workday is selected */}
                    {selectedCard === "workday" && (
                        <div
                            className="mx-auto"
                            style={{
                                width: "640px",
                                height: "0px",
                                borderTop: "1px solid #E2E8F0",
                                opacity: 1,
                                marginTop: "48px",
                                marginBottom: "36.8px"
                            }}
                        ></div>
                    )}

                    {/* Button Container - matches erp-button-container with exact margin */}
                    <div className="text-center" style={{ marginTop: selectedCard === "workday" ? "0" : "100px" }}>
                        <button
                            type="submit"
                            className="btn-primary no-underline"
                        >
                            Next
                        </button>
                    </div>
                </form>
            </div>
        </div>
    );
};

export default ModuleStep;
